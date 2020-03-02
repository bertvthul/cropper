# Simple upload and crop images in Laravel

A simple uploader for your images in Laravel. No database columns needed, due to saving the images using the field name, the id and using the model name as the folder to save in.  

```html
@cropper('avatar', 'App\User')
```

In the associated model you set up the preferences;

```php
use Bertvthul\Cropper\HasCropper;

class User
{
    use HasCropper;

    public static $cropper = [
        'avatar' => [
            'validation' => [
                'required' => true,
            ],
            'width' => 200,
            'height' => 200,
        ],
    ];
```

Images are automatically saved in a folder with the model name. The filenames are formatted using the id and the fieldname;
```
public
    images
        user
            1-avatar.jpg
            1-avatar-orig.jpg
```

The original file is saved (`-orig` at the end), in a max of 2000x2000 pixels, for later resizing. So when editing an item, you can recrop the uploaded image.

## Installation

You can install the package via composer:

```bash
composer require bertvthul/cropper
```

Add the service provider to the providers array in `config\app.php`;

```php
Bertvthul\Cropper\CropperServiceProvider::class,
```

Also make sure the js and css are loaded, by adding them to app.js and app.scss;

```js
require('./../../vendor/bertvthul/cropper/src/js/cropper.js');
```
```css
@import './../../vendor/bertvthul/cropper/src/css/cropper.scss';
```

## Usage

In your blade file you can add the upload field;

```html
@cropper('avatar', 'App\User', ['class' => 'form-control', 'id' => $user->id])
```

In this example the fieldname is `avatar`. As a second parameter define the model that is associated, where you can define the settings. The third parameter is optional for passing settings. Supported settings are;

```php
[
    'class' => 'form-control', // separated by spaces
    'id' => $user->id, // useful when the right id isn't auto discovered
]
```

In the model you can set up the preferences in a `$cropper` variable, where the key is the name of the field;

```php
use Bertvthul\Cropper\HasCropper;

class User
{
    use HasCropper;

    public static $cropper = [
        'avatar' => [
            'width' => 200,
            'height' => 200,
        ],
        'cover' => [
            'width' => 1600,
            'height' => 600,
            'validation' => [
                'required' => false,
            ]
        ]
    ];
```

In this example the fieldname is `avatar`. Default field settings are;
```php
'validation' => [
    'filetypes' => 'jpeg,png,jpg,gif,svg',
    'max'       => 20480, // 20 mb
    'required'  => true,
],
'width' => 200,
'height' => 200,
'upload-text' => 'Upload',
```

In your blade files, you can simply use the image getter;
```html
<img src="{{ $user->cropper->avatar->path }}">
```
Available are;
- path
- path_original (path to the original uncropped image)
- ext
- name

## Features

###### Inline editing
Editing the images is also possible inline. This means you don't have to place it in a form. The package then saves the changes on the fly. Usefull when you want users to easily update their image. 

###### No database columns needed
Using a logical naming convention, so no need to save the image in the database.

## License

The MIT License (MIT).