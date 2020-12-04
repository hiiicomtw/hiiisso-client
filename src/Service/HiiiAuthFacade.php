<?php


namespace Hiiicomtw\HiiiSSOClient\Service;

use Illuminate\Support\Facades\Facade;

class HiiiAuthFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return HiiiAuthService::class;
    }

}