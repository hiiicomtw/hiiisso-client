<?php

namespace Hiiicomtw\HiiiSSOClient\Service;

interface HiiiAuthFactory
{
    public function driver($driver = null);
}