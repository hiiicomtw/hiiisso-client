<?php


namespace Hiiicomtw\HiiiSSOClient\Service;

use Illuminate\Support\ServiceProvider;

class HiiiAuthServerProvider extends ServiceProvider
{
    public function boot()
    {}

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = dirname(__DIR__) . '/config/hiiisso-client.php';
    }

    public function register()
    {
    	$this->mergeConfigFrom($this->config, 'hiiisso-client');
        $this->app->singleton(HiiiAuthService::class, function(){
            return new HiiiAuthService;
        });

    }

    public function provides()
    {
        return [HiiiAuthService::class];
    }

}
