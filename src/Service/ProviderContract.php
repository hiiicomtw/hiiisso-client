<?php


namespace Hiiicomtw\HiiiSSOClient\Service;


interface ProviderContract
{
    public function redirect();
    
    public function user();

}