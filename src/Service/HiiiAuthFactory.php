<?php

namespace Hiiicomtw\HiiiSSOClient\Service;

interface HiiiAuthFactory
{
//    public function guard($name = null);
//    public function shouldUse($name = null);
    public function driver($driver = null);
}
