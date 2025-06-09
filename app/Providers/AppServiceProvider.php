<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\InventarioService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(InventarioService::class, function ($app) {
            return new InventarioService();
        });
    }
}