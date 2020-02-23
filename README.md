# Simple upload and crop your images

A simple uploader for your images in Laravel. Simply add the following to your blade file; 

```html
@cropper(['avatar', 'App\User', ['class' => 'form-control', 'id' => $user->id]])
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

## Installation

You can install the package via composer:

```bash
composer require bertvthul/cropper
```

Add the service provider to the providers array in `config\app.php`;

```php
Bertvthul\Cropper\CropperServiceProvider::class,
```

And make sure the js is loaded by adding the following to your app.js;

```js
require('./../../vendor/bertvthul/cropper/src/js/cropper.js');
```

And to add the basic styling, add the following to your app.scss;

```css
@import './../../vendor/bertvthul/cropper/src/css/cropper.scss';
```

## Usage

In your blade file you can add the upload field;

```html
@cropper(['avatar', 'App\User', ['class' => 'form-control', 'id' => $user->id]])
```

In this example the fieldname is `avatar`. As a second parameter define the model that is associated. In the model you can set up the preferences in a `$cropper` variable;

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

In this example the fieldname is `avatar`. The key is the fieldname and the content of the array are the variables. The default settings are;
```php
'validation' => [
    'filetypes' => 'jpeg,png,jpg,gif,svg',
    'max'       => 2048,
    'required'  => true,
],
'width' => 200,
'height' => 200,
```   


## License

The MIT License (MIT).