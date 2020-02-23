<?php

namespace Bertvthul\Cropper;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Image;

class CropperDirectives
{
    public static function cropper($data)
    {
		$name = 'avatar';
		$class = null;
        $modelName = '';
        $id = 0;
        $width = 200;
        $height = 200;
        $ratio = 1;

		if (!empty($data) && is_array($data)) {
    		$name = $data[0] ?? null;
            if (!empty($data[1])) {
                $modelName = $data[1];
            } else {
                $action = request()->route()->getAction();
                $controllerMethod = class_basename($action['controller']);
                $controllerName = explode('@', $controllerMethod)[0];
                $modelName = 'App\\' . ucfirst(explode('_', Str::snake($controllerName))[0]);
            }
            if (!empty($data[2])) {
                $class = $data[2]['class'] ?? $class;
                if (!empty($data[2]['id'])) {
                    $id = $data[2]['id'];
                }
            }
		} else {
            // assume the data is the name of the field
            $name = $data;
        }

        // Get the settings from the model (width, height)
        $imageStyle = 'normal';
        if (!empty($modelName)) {
            $model = app()->make($modelName);
            if(gettype($model) == 'object') {
                $settings = $model::$cropper;
                $width = $settings[$name]['width'];
                $height = $settings[$name]['height'];
                $cropRatio = (100 / $width) * $height;

                if ($cropRatio < 75 && $width > 800) {
                    $imageStyle = 'wide-large';
                } elseif ($cropRatio < 100) {
                    $imageStyle = 'wide';
                } elseif ($cropRatio == 100) {
                    $imageStyle = 'square';
                } elseif ($cropRatio > 120 && $height > 800) {
                    $imageStyle = 'high-large';
                } else {
                    $imageStyle = 'high';
                }
            }
        }

        // Get the id dynamic
        if (empty($id)) {
            if (!is_null(request()->route('id'))) {
                $id = request()->route('id');
            } elseif(!empty(request()->route()->originalParameters())) {
                foreach(request()->route()->originalParameters() as $paramName => $paramValue) {
                    if (!empty((int)$paramValue)) {
                        $id = (int)$paramValue;
                        break;
                    }
                }
            }
        }

        // get image path
        $imagePath = '';
        $imagePathCropped = '';
        $imagePaths = $model->getCropperPathsAttribute($id);
        if (!empty($imagePaths[$name]['temp'])) {
            $imagePath = $imagePaths[$name]['temp'];
        } elseif (!empty($imagePaths[$name]['original'])) {
            $imagePathCropped = $imagePaths[$name]['path'];
            $imagePath = $imagePaths[$name]['original'];
        }
        if (!empty($imagePaths[$name]['old-temp'])) {
            // Oude tijdelijke beelden; verwijderen...
            $model->deleteTempImages($id);
        }

        // html
        $html = '';
        $html .= '<div class="cropper-con' . (empty($imagePath) ? ' no-image-yet' : '') . ' image-style-' . $imageStyle . '" data-model="' . $modelName . '" data-name="' . $name . '" data-id="' . $id . '">';
        $html .= '<div class="cropper-slice-html-example">';
        $html .= $model->getCropperSliceHtml($name, $imagePath, $imagePathCropped);
        $html .= '</div>';
        $html .= '<input type="hidden" name="cropperx[' . $name . ']" class="cropperx" placeholder="x positie" value="' . old('cropperx.' . $name) . '">';
        $html .= '<input type="hidden" name="croppery[' . $name . ']" class="croppery" placeholder="y positie" value="'. old('croppery.' . $name) . '">';
		$html .= '<input type="file" class="img-cropper' . (!is_null($class) ? ' ' . $class : '') . '" name="cropper[' . $name . ']" id="cropper-' . $name . '">';
        $html .= '</div>';

		return $html;
    }
}