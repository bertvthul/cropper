<?php

namespace Bertvthul\Cropper;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Image;

class CropperDirectives
{
    public static function cropper(string $name, string $modelName, ?array $options = [])
    {
        $model = app()->make($modelName);

        if(gettype($model) != 'object') {
            // Model not found
            return;
        }

        $settings = $model::$cropper ?? [];

        if (empty($settings)) {
            // No settings in the model
            return;
        }

        $id = !empty($options['id']) ? $options['id'] : $model->getId();
        $width = $settings[$name]['width'];
        $height = $settings[$name]['height'];
        $cropRatio = (100 / $width) * $height;

        if ($cropRatio < 75 && $width > 800) {
            $imageStyle = 'landscape';
        } elseif ($cropRatio < 100) {
            $imageStyle = 'wider';
        } elseif ($cropRatio == 100) {
            $imageStyle = 'square';
        } elseif ($cropRatio > 120 && $height > 800) {
            $imageStyle = 'portrait';
        } else {
            $imageStyle = 'higher';
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
            // Delete old temp images
            $model->deleteTempImages($id);
        }

        $hasModal = false;
        if (\Config::get('cropper.media_library.active') || \Config::get('cropper.stock_library.active')) {
            $hasModal = true;
        }

        $extraClasses = [];
        $extraClasses[] = 'cropper--' . $imageStyle;
        $extraClasses[] = 'cropper--' . $name;
        if (!empty($settings[$name]['class'])) {
            $extraClasses[] = $settings[$name]['class'];
        }
        if (empty($imagePath)) {
            $extraClasses[] = 'cropper--no-image';
        }
        if (!empty($options['inline-save'])) {
            $extraClasses[] = 'cropper--inline-save';
        }
        if (!empty($options['class'])) {
            $extraClasses[] = $options['class'];
        }
        if ($hasModal) {
            $extraClasses[] = 'cropper--has-modal';
        }
        if (\Config::get('cropper.media_library.active')) {
            $extraClasses[] = 'cropper--has-media-library';
        }
        if (\Config::get('cropper.stock_library.active')) {
            $extraClasses[] = 'cropper--has-stock-library';
        }
        
        // html
        $html = '';
        $html .= '<div class="cropper ' . implode(' ', $extraClasses) . '" data-model="' . $modelName . '" data-name="' . $name . '" data-id="' . $id . '">';

            $html .= '<div class="cropper__load-indicator fa-2x"><i class="fas fa-circle-notch fa-spin"></i></div>';
            $html .= '<div class="cropper__editor">';
                $html .= $model->getCropperSliceHtml($name, $imagePath, $imagePathCropped);
            $html .= '</div>';

            $html .= '<input type="hidden" name="cropperx[' . $name . ']" class="cropperx" placeholder="x positie" value="' . old('cropperx.' . $name) . '">';
            $html .= '<input type="hidden" name="croppery[' . $name . ']" class="croppery" placeholder="y positie" value="'. old('croppery.' . $name) . '">';
    		$html .= '<input type="file" class="img-cropper" name="cropper[' . $name . ']" id="cropper-' . $name . '">';

        $html .= '</div>';

        // $html .= PixabayMedia::listImages('wielrennen');

		return $html;
    }
}