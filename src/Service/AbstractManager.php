<?php


namespace Hiiicomtw\HiiiSSOClient\Service;

use Illuminate\Support\Str;
use Closure;
use Hiiicomtw\HiiiSSOClient\Exceptions\InvalidArgumentException;

abstract class AbstractManager
{
    protected $app;
    protected $drivers = [];

    public function __construct($app)
    {
        $this->app = $app;
    }

    abstract public function getDefaultDriver();

    public function driver($driver = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    protected function createDriver($driver)
    {
        // We'll check to see if a creator method exists for the given driver. If not we
        // will check for a custom driver creator, which allows developers to create
        // drivers using their own customized driver creator Closure to create it.
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        } else {
            $method = 'create'.Str::studly($driver).'Driver';

            if (method_exists($this, $method)) {
                return $this->$method();
            }
        }
        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }

    public function getDrivers()
    {
        return $this->drivers;
    }

    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}