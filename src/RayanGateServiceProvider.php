<?php

namespace Rayanpay\RayanGate;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;


class RayanGateServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/errors.php','errors-gateway');
        $this->mergeConfigFrom(__DIR__.'/config/config.php','config-gateway');
    }

    public function boot()
    {
        $this->loadDependencies()
            ->publishDependencies();
    }

    private function loadDependencies()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');

         $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

         $this->loadViewsFrom(__DIR__.'/resources/views','rayan-gate');

         $this->publishes([
             __DIR__.'/resources/views' => base_path('/resources/views/rayan-gate')
         ],'rayan-gate-views');


        return $this;
    }

    private function publishDependencies(){

        $this->publishes([
            __DIR__.'/Database/migrations' => database_path('/migrations')
        ], 'user-migration');

        $this->publishes([
            __DIR__.'/config/errors.php' => config_path('errors.php'),
        ],'gatway-error');
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('config-gatway.php'),
        ],'gatway-config');

    }

}
