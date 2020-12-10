<?php


namespace Hiiicomtw\HiiiSSOClient\Service;


interface ProviderInterface
{
    public function redirect();

    public function user();
}
