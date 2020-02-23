<?php

namespace Bertvthul\Cropper;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CropperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    public function boot()
    {
        $directives = CropperDirectives::class;
        foreach(get_class_methods($directives) as $method) {
            Blade::directive($method, function ($expression) use ($directives, $method) {
                return "<?php echo $directives::$method($expression); ?>";
            });
        }

        $this->app->make('Bertvthul\Cropper\CropperController');
    }
}
