<?php

namespace Bertvthul\Cropper;

use Illuminate\Support\Facades\Storage;
use Image;

trait HasCropper
{
    private $imagesFields = [];
    private $startId = 0;
    private $defaultSettings = [
        'validation' => [
            'filetypes' => 'jpeg,png,jpg,gif,svg',
            'max'       => 20480, // 20 mb
            'required'  => true,
        ],
        'width' => 200,
        'height' => 200,
        'upload-text' => 'Upload afbeelding',
        'class' => '',
    ];

    public static function bootHasCropper()
    {
        static::saving(function ($model) {
            $model->beforeCropImages();
        });

        static::saved(function ($model) {
            $model->cropImages();
        });

        static::deleted(function ($model) {
            $model->deleteImages();
        });

        static::retrieved(function($model) {
            $model->initCropper();
        });
    }

    protected function initCropper() {

    }

    protected function beforeCropImages()
    {
        $cropperFields = static::$cropper;

        if (empty($cropperFields)) {
            return;
        }

        $this->startId = $this->getId();

        foreach($cropperFields as $name => $settings) {
            $this->validateImage($name);
        }
    }

    protected function cropImages()
    {
        $cropperFields = static::$cropper;

        if (empty($cropperFields)) {
            return;
        }

        foreach($cropperFields as $name => $settings) {
            $this->cropAndUpload($name);
        }

        $this->deleteTempImages($this->id);
        $this->deleteTempImages(0);
    }

    protected function deleteImages() 
    {
        $cropperFields = static::$cropper;

        if (empty($cropperFields)) {
            return;
        }

        foreach($cropperFields as $name => $settings) {
            $id = $this->getId();
            $images = glob(public_path() . '\images\\' . $this->getModelFolderName() . '\\' . $id . '-' . $name . '*');
            foreach($images as $image) {
                unlink($image);
            }
        }
    }

    public function deleteTempImages($id) 
    {
        $cropperFields = static::$cropper;

        if (empty($cropperFields)) {
            return;
        }

        foreach($cropperFields as $name => $settings) {
            $images = glob(public_path() . '\images\\' . $this->getModelFolderName() . '\\' . $id . '-' . $name . '-temp*');
            foreach($images as $image) {
                unlink($image);
            }
        }
    }

    public function getCropperAttribute($id = 0) {
        $id = !empty($id) ? $id : $this->id;
        $mutators = [];

        if (empty(static::$cropper)) {
            return $mutators;
        }

        foreach (static::$cropper as $field_name => $field_settings) {
            if ($ext = $this->getImageExtension($id . '-' . $field_name)) {
                $image_url = asset($this->getFolder() . '/' . $id . '-' . $field_name . '.' . $ext); 
                $mutators[$field_name] = '<img src="' . $image_url . '" style="max-width: 100%; height: auto;">';
            } else {
                $mutators[$field_name] = '';
            }
        }

        return $mutators;
    }

    public function getCropperPathsAttribute($id = 0) {
        $id = !empty($id) ? (int)$id : (int)$this->id;
        $mutators = [];

        if (empty(static::$cropper)) {
            return $mutators;
        }

        foreach (static::$cropper as $field_name => $field_settings) {
            if (!empty(old()) && $ext = $this->getImageExtension($id . '-' . $field_name . '-temp')) {
                // get the temp image
                $image_url = $this->getFolder() . '/' . $id . '-' . $field_name . '-temp.' . $ext; 
                $mutators[$field_name]['path'] = $image_url;
                $mutators[$field_name]['asset'] = asset($image_url);
                $mutators[$field_name]['temp'] = $image_url;
            } elseif ($ext = $this->getImageExtension($id . '-' . $field_name)) {
                $image_url = $this->getFolder() . '/' . $id . '-' . $field_name . '.' . $ext; 
                $mutators[$field_name]['path'] = $image_url;
                $mutators[$field_name]['asset'] = asset($image_url);
                $mutators[$field_name]['original'] = $this->getFolder() . '/' . $id . '-' . $field_name . '-orig.' . $ext;

            }
            if (empty(old()) && $tempExt = $this->getImageExtension($id . '-' . $field_name . '-temp')) {
                $mutators[$field_name]['old-temp'] = $this->getFolder() . '/' . $id . '-' . $field_name . '-temp.' . $tempExt;
            }
        }

        return $mutators;
    }

    private function getFolder() {
        $modelFolderName = $this->getModelFolderName();

        return 'images/' . $modelFolderName;
    }

    private function getModelFolderName() {
        $className = get_class($this);
        $reflection = new \ReflectionClass($className);

        return strtolower($reflection->getShortName());
    }

    private function getImageExtension($name) {
        $image = glob(public_path() . '\images\\' . $this->getModelFolderName() . '\\'. $name . '.*');
        if (empty($image[0])) {
            return false;
        }

        return pathinfo($image[0], PATHINFO_EXTENSION);
    }

     private function getSettings($name): array
     {
        $settings = !empty(static::$cropper[$name]) ? 
            array_replace_recursive($this->defaultSettings, static::$cropper[$name]) : 
            $this->defaultSettings;

        return $settings;
    }

    private function validateImage($name, $settings = []) 
    {
        $validationRulesAll = [
            'cropperx.' . $name => 'numeric|min:0|max:100|nullable',
            'croppery.' . $name => 'numeric|min:0|max:100|nullable',
        ];
        $image = request('cropper.' . $name);
        if (is_null($image)) {
            // uploaded image
            $imageTemp = $this->getTempImage($name);
        } else {
            // image uploaded
            $validationRules = $this->getValidationRules($name, $this->getId());
            $validationRulesAll['cropper.' . $name] = implode('|', $validationRules);
        }

        return request()->validate($validationRulesAll);
    }

    private function getTempImage($name) 
    {
        $imageNameTemp = $this->getId() . '-' . $name . '-temp';
        if ($ext = $this->getImageExtension($imageNameTemp)) {
            $imageTempPath = $this->getFolder() . '/' . $imageNameTemp . '.' . $ext;
            return $imageTempPath;
        }

        return '';
    }

    private function getValidationRules($name, $id = 0) 
    {
        $settings = $this->getSettings($name);

        $validationRules = ['image'];

        foreach($settings['validation'] as $validationName => $validationValue) {
            if ($validationName == 'required' && !empty($validationValue) && !$id) {
                $validationRules[] = 'required';
            } elseif($validationName == 'filetypes' && !empty($validationValue)) {
                $validationRules[] = 'mimes:' . $validationValue;
            } elseif($validationName == 'max' && !empty($validationValue)) {
                $validationRules[] = 'max:' . $validationValue;
            } elseif(!in_array($validationName, ['required', 'filetypes', 'max'])) {
                $validationRules[] = $validationValue;
            }
        }

        return $validationRules;
    }

    private function uploadImage($name)
    {
        $image = request('cropper.' . $name);

        // image name
        $imageName = $this->getId() . '-' . $name . '-orig.' . $image->extension();
        $image->move(public_path($this->getFolder()), $imageName);
    }

    private function deleteOldImages($name): void
    {
        // Delete old images (not the temp(!), because its used for upload)
        $id = $this->getId();
        $imageLocation = public_path() . '\images\\' . $this->getModelFolderName() . '\\';

        $oldImage = glob($imageLocation . $id . '-' . $name . '.*');
        if(!empty($oldImage[0])) {
            unlink($oldImage[0]);
        }

        $oldOrigImage = glob($imageLocation . $id . '-' . $name . '-orig.*');
        if(!empty($oldOrigImage[0])) {
            unlink($oldOrigImage[0]);
        }
    }

    private function cropAndUpload($name)
    {
        $id = $this->getId();
        $image = request('cropper.' . $name);
        if (is_null($image)) {
            // check if there is an old temp image to use
            $imageNameTemp = $this->startId . '-' . $name . '-temp';
            if ($ext = $this->getImageExtension($imageNameTemp)) {
                $imageTempPath = $this->getFolder() . '/' . $imageNameTemp . '.' . $ext;
                $image = Image::make($imageTempPath);
                $image_real_path = $imageTempPath;
            }
        } else {
            $ext = $image->extension();
            $image_real_path = $image->getRealPath();
        }

        if (!is_null($image)) {
            // wis bestaande afbeelding
            $this->deleteOldImages($name);
        }

        if (is_null($image)) {
            if (!is_null(request('cropperx.' . $name)) || !is_null(request('croppery.' . $name))) {
                // Crop een bestaand beeld
                $imageNameOriginal = $id . '-' . $name . '-orig';
                $ext = $this->getImageExtension($imageNameOriginal);
                if (!$ext) {
                    // Geen eerder geupload bestand
                    return;
                }
                $imageOriginalPath = $this->getFolder() . '/' . $imageNameOriginal . '.' . $ext;
                $imageCrop = Image::make($imageOriginalPath);
                return $this->cropToFit($name, $imageCrop, $imageCrop->extension);
            }
            return;
        }
        $settings = $this->getSettings($name);

        $imageNameOriginal = $id . '-' . $name . '-orig.' . $ext;
        $destinationPath = public_path($this->getFolder());

        // Save cropped image
        // $croppedImage = Image::make($image->getRealPath());
        // $croppedImage->fit($settings['width'], $settings['height'], function($constraint){
        //     $constraint->aspectRatio();
        // })->save($destinationPath . '/' . $imageName);

        $croppedImage = Image::make($image_real_path);
        $this->cropToFit($name, $croppedImage, $ext);

        // Save original image (resized)
        $resizedImage = Image::make($image_real_path);
        $width = 2000;
        $height = 2000;
        if ($resizedImage->width() > $resizedImage->height()) {
            $width = null;
        } elseif ($resizedImage->width() < $resizedImage->height()) {
            $height = null;
        }
        $resizedImage->resize($width, $height, function($constraint){
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save($destinationPath . '/' . $imageNameOriginal);

        return true;
    }

    private function cropToFit($name, $croppedImage, $ext)
    {
        $settings = $this->getSettings($name);
        if (is_null($croppedImage)) {
            return;
        }

        $imageName = $this->getId() . '-' . $name . '.' . $ext;
        $destinationPath = public_path($this->getFolder());

        $posX = (int)request('cropperx.' . $name); // percentage van links
        $posY = (int)request('croppery.' . $name); // percentage van boven
        
        $width = $settings['width'];
        $height = $settings['height'];
        $x = 0;
        $y = 0;
        $imageRatio = $croppedImage->width() / $croppedImage->height();
        $cropRatio = $width / $height;

        if ($imageRatio > $cropRatio) {
            // Afbeelding is breder dan te croppen gebied
            $leftSpace = ($height * $imageRatio) - $width; // wat er nog over is rechts
            $x = $leftSpace * ($posX / 100);
            $width = null;
        } elseif ($imageRatio < $cropRatio) {
            // Afbeelding is hoger dan te croppen gebied
            $imageRatio = $croppedImage->height() / $croppedImage->width(); 
            $leftSpace = ($width * $imageRatio) - $height; // wat er nog over is onder
            $y = $leftSpace * ($posY / 100);
            $height = null;
        }

        $croppedImage->resize($width, $height, function($constraint){
            $constraint->aspectRatio();
        });
        $croppedImage->crop($settings['width'], $settings['height'], (int)$x, (int)$y);
        $croppedImage->save($destinationPath . '/' . $imageName);
    }

    private function getId() {
        return !empty($this->attributes['id']) ? (int)$this->attributes['id'] : 0;
    }

    public function xhrUploadCropper() {
        $name = request('name');
        $image = request('file');
        $id = request('id');

        // validate
        $validationRules = $this->getValidationRules($name);

        $validated = request()->validate([
            'file' => implode('|', $validationRules),
        ]);

        // Delete old temp image
        $oldImage = glob(public_path() . '\images\\' . $this->getModelFolderName() . '\\' . $id . '-' . $name . '-temp.*');
        if(!empty($oldImage[0])) {
            unlink($oldImage[0]);
        }

        // props
        $ext = $image->extension();
        $imageName = $id . '-' . $name . '-temp.' . $ext;
        $destinationPath = public_path($this->getFolder());

        // Save original image (resized)
        $resizedImage = Image::make($image->getRealPath());
        $width = 2000;
        $height = 2000;
        if ($resizedImage->width() > $resizedImage->height()) {
            $width = null;
        } elseif ($resizedImage->width() < $resizedImage->height()) {
            $height = null;
        }
        $resizedImage->resize($width, $height, function($constraint){
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save($destinationPath . '/' . $imageName);

        return $this->getFolder() . '/' . $imageName;
    }

    public function getCropperSliceHtml($name, $imagePath, $imagePathCropped = '') {
        $settings = $this->getSettings($name);
        $width = $settings['width'];
        $height = $settings['height'];
        $cropRatio = (100 / $width) * $height;
        $cropRatioRev = (100 / $height) * $width;
        if(!empty($imagePath)) {
            $image = Image::make($imagePath);
            $imageRatio = (100 / $image->width()) * $image->height();
            $imageRatioRev = (100 / $image->height()) * $image->width();
        } else {
            $imageRatio = 1;
            $imageRatioRev = 1;
        }

        $extraClasses = [];
        $extraClasses[] = 'cropper__crop--' . $name;
        $extraClasses[] = 'cropper__crop--' . ($imageRatio > $cropRatio ? 'higher' : 'wider');
        if (empty($imagePathCropped) || (!is_null(old('cropperx.' . $name)) || !is_null(old('croppery.' . $name)))) {
            $extraClasses[] = 'cropper__crop--cropping';
        }

        $html = '';
        if (empty($imagePath)) {
            $html .= '<label for="cropper-' . $name . '" class="cropper__upload"><span class="btn btn-sm btn-success"><i class="fa fa-upload"></i></span> ' . $settings['upload-text'] . '</label>';
        }

        $html .= '<div class="cropper__crop ' . implode(' ', $extraClasses) . '">';

            $html.= '<div class="cropper__example"' . ($imagePathCropped ? ' style="background-image:url('. asset($imagePathCropped) .');"' : '') . '></div>';
            $html.= '<div class="cropper__image-con" style="padding-top:' . (round($cropRatio * 100) / 100) . '%;">';
                $html .= '<div class="cropper__image" style="' . ($imageRatio > $cropRatio ? 'width: 100%;padding-top:' . (round($imageRatio * 100) / 100) . '%;' : 'width: ' . ((100 / $cropRatioRev) * $imageRatioRev) . '%;height:100%;' ) . 'background-image: url(' .  asset($imagePath) . '?' . rand(10000,99999) . ');"></div>';
            $html.= '</div>';

            $html .= '<div class="cropper__croptools">';
                $html .= '<span class="btn btn-arrow-up"><i class="fa fa-chevron-up"></i></span>';
                $html .= '<span class="btn btn-arrow-down"><i class="fa fa-chevron-down"></i></span>';
            $html .= '</div>';


            $html.= '<div class="cropper__options">';
                $html .= '<label class="cropper__upload btn btn-sm btn-success" for="cropper-' . $name . '" title="' . $settings['upload-text'] . '"><i class="fa fa-upload"></i></label>';
                
                $html.= '<div class="cropper__recrop btn btn-sm btn-success" title="Opnieuw uitsnijden (' . $width . '&times;' . $height . ')"><i class="fa fa-crop"></i></div>';
                
                $html.= '<span class="cropper__savecrop btn btn-sm btn-success" title="Uitsnede opslaan"><i class="fa fa-check"></i></span>';
            $html.= '</div>';

        $html .= '</div>';

        return $html;
    }
}