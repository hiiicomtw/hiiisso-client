<?php


namespace Hiiicomtw\HiiiSSOClient\Service;

use Hiiicomtw\HiiiSSOClient\Service\HiiiAuthFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class HiiiAuthServerProvider extends ServiceProvider
{

    private $config = null;

    public function boot()
    {
        $this->publishes([
            $this->config => config_path('hiiisso-client.php')
        ], 'hiiisso-client');
    }

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = dirname(__DIR__) . '/config/hiiisso-client.php';
    }

    public function register()
    {
    	$this->mergeConfigFrom($this->config, 'hiiisso-client');
    	$config = $this->config;
        $this->app->singleton(HiiiAuthFactory::class, function($app) use($config){
            return new HiiiAuthService($app, $config);
        });

    }

    public function provides()
    {
        return [HiiiAuthFactory::class];
    }

}
