<?php


namespace Hiiicomtw\HiiiSSOClient\Service;

use Illuminate\Support\Facades\Facade;

class HiiiAuthFacade extends Facade
{
	/**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return HiiiAuthService::class;
    }

}
