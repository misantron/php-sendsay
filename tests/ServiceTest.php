<?php

namespace Sendsay\Tests;

use Sendsay\Service;

class ServiceTest extends BaseTestCase
{
    public function testConstructor()
    {
        $service = new Service('foo', 'bar', 'test', [
            'log.name' => 'api.sendsay'
        ]);
    }
}