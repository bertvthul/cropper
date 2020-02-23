# Crop your images

Crop your images before upload. 

```html
@cropper('avatar', ['width' => 500, 'height' => 500])
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

## Usage

## License

The MIT License (MIT).