<?php

declare(strict_types = 1);

namespace circlesandlambdas\larotp;

use circlesandlambdas\larotp\Console\CreateSymmKey;
use circlesandlambdas\larotp\Http\Middleware\LarOTPKeeper;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;


class LarOTPServiceProvider extends ServiceProvider{
    /**
     * Register Services
     */
    public function register(): void{
        $this->app->singleton(LarOTP::class, function($app){
            return new LarOTP();
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void{
        $this->registerSetup();
        $this->registerMiddleware();
        $this->registerResources();
        $this->registerRoutes();
        $this->registerCommands();
    }

    protected function registerSetup(){
        $this->publishes([
            __DIR__.'/config/larotp.php' => config_path('larotp.php'),
        ], 'larotp-config');
        
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'larotp-migrations');
    }

    public function registerMiddleware(){
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('larotp.keeper', LarOTPKeeper::class);
    }

    protected function registerResources(){
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'larotp');
    }

    protected function registerRoutes(){
        Route::group($this->routeConfiguration(), function(){
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    protected function registerCommands(){
        if ($this->app->runningInConsole()) {
                $this->commands([
                    CreateSymmKey::class,
                ]);
        }
    }

    protected function routeConfiguration(){
        return [
            'prefix' => 'larotp',
            'middleware' => ['web'],
        ];
    }
}