<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use DB;
use Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
         DB::listen(function ($query) {
            // $query->sql
            // $query->bindings
            // $query->time
            Log::info('Time:'.$query->time);
            Log::info('SQL:'.$query->sql);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
         if ($this->app->environment() == 'local' || $this->app->environment() == 'production')
   	 {
    		$this->app->register('Barryvdh\Debugbar\ServiceProvider');
   	 }
    }
}
