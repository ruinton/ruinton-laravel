<?php

namespace Ruinton;

use Illuminate\Support\ServiceProvider;
use Ruinton\Middleware\QueryStringParserMiddleware;

class RuintonServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['router']->aliasMiddleware('ruinton_params', QueryStringParserMiddleware::class);
//        $this->app->singleton(Connection::class, function ($app) {
//            return new Connection(config('riak'));
//        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        View::composer('view', function () {
//            //
//        });
    }
}
