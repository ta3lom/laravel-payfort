<?php


namespace MoeenBasra\Payfort;

use Illuminate\Support\ServiceProvider;

class PayfortServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishing();
    }

    public function registerPublishing()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'payfort-lang');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/payfort.php' => config_path('payfort.php'),
            ], 'payfort-config');
        }
    }

    public function register()
    {
        $this->configure();

        $this->app->singleton('payfort', function () {
            return new Payfort(config('payfort'));
        });
    }

    private function configure()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/payfort.php', 'payfort');
    }

    public function provides()
    {
        return ['payfort'];
    }
}
