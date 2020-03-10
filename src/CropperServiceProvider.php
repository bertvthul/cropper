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
        // Directives
        $directives = CropperDirectives::class;
        foreach(get_class_methods($directives) as $method) {
            Blade::directive($method, function ($expression) use ($directives, $method) {
                return "<?php echo $directives::$method($expression); ?>";
            });
        }

        // Controllers
        $this->app->make('Bertvthul\Cropper\CropperController');

        // Config
        $this->publishes([
            __DIR__.'/cropper-config.php' => config_path('cropper.php'),
        ]);
    }
}
