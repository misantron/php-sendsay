<?php

namespace Sendsay\Tests\Client;

use Sendsay\Client\Client;
use Sendsay\Tests\BaseTestCase;

class ClientTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithInvalidCredentials()
    {
        new Client(null, ['log.path' => 'api.sendsay.log']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructor()
    {
        new Client([
            'login' => 'foo',
            'sublogin' => 'bar',
            'passwd' => 'test',
        ], [
            'log.path' => __DIR__ . '/../../logs/api.sendsay.log'
        ]);
    }
}