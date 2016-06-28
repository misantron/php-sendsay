<?php

namespace Sendsay\Tests;

date_default_timezone_set('UTC');

class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Call protected class method using reflection
     *
     * @param string $obj
     * @param string $name
     * @param array $args
     * @return mixed
     */
    protected function callMethod($obj, $name, $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs(null, $args);
    }

    /**
     * @param string $class
     * @param string $name
     * @return mixed
     */
    protected function getProperty($class, $name)
    {
        $property = new \ReflectionProperty($class, $name);
        $property->setAccessible(true);
        return $property->getValue($class);
    }
}