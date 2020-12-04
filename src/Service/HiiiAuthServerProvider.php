<?php


namespace Hiiicomtw\HiiiSSOClient\Service;

use Illuminate\Support\ServiceProvider;

class HiiiAuthServerProvider extends ServiceProvider
{
    public function boot()
    {}

    public function register()
    {
        $this->app->singleton(HiiiAuthService::class, function(){
            return new HiiiAuthService;
        });

    }

}