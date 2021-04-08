<?php

namespace WS\Datagrid;

use Illuminate\Support\ServiceProvider;

class DatagridServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
      $this->loadViewsFrom(__DIR__.'/views', 'datagrid');
      $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'datagrid');
      
      $this->publishes([
          __DIR__.'/views' => base_path('resources/views/vendor/datagrid'),
          __DIR__.'/config/datagrid.php' => config_path('datagrid.php'),
          __DIR__.'/resources/lang/it/datagrid.php' => resource_path('lang/vendor/it/datagrid.php'),
          __DIR__.'/resources/lang/en/datagrid.php' => resource_path('lang/vendor/en/datagrid.php'),
      ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
