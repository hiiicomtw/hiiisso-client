<?php


namespace Hiiicomtw\HiiiSSOClient\Service;

use Hiiicomtw\HiiiSSOClient\Helper\DataHelper;
use Hiiicomtw\HiiiSSOClient\Exceptions\InvalidArgumentException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class HiiiAuthManager extends AbstractManager implements ManagerFactory
{
    public function with($driver)
    {
        return $this->driver($driver);
    }

    protected function createAdminDriver()
    {
        $config = $this->formatConfig($this->app['config']['hiiisso-client.admin']);
        return $this->buildProvider(AdminProvider::class, $config);
    }

    protected function createCustomerDriver()
    {
        $config = $this->formatConfig($this->app['config']['hiiisso-client.customer']);
        return $this->buildProvider(CustomerProvider::class, $config);
    }

    public function buildProvider($provider, $config)
    {
        return new $provider($this->app['request'], $config);
    }

    public function getDefaultDriver()
    {
        throw new InvalidArgumentException('No driver was specifaied');
    }

    protected function formatConfig(array $config)
    {
        if(!DataHelper::keyValueIsEmpty('redirect', $config)){
            $redirect = value($config['redirect']);
            $config['redirect'] = Str::startsWith($redirect, '/')
                ? $this->app['url']->to($redirect)
                : $redirect;
        }
        if(!DataHelper::keyValueIsEmpty('fail_redirect', $config)){
            $redirect = value($config['fail_redirect']);
            $config['fail_redirect'] = Str::startsWith($redirect, '/')
                ? $this->app['url']->to($redirect)
                : $redirect;
        }
        return $config;
    }

    protected function formatRedirectUrl($redirect)
    {
        return Str::startsWith($redirect, '/')
            ? $this->app['url']->to($redirect)
            : $redirect;
    }

}
