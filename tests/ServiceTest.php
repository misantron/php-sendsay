<?php

namespace Sendsay\Tests;

use Sendsay\Service;

date_default_timezone_set('UTC');

class ServiceTest extends BaseTestCase
{
    /** @var Service */
    private $service;

    protected function setUp()
    {
        parent::setUp();

        $options = [
            'login' => '',
            'password' => '',
            'log.file.path' => '\\log\\sendsay.log',
        ];

        $this->service = new Service($options);
    }

    public function testGetUser()
    {
        $expected = null;
        $actual = $this->service->getUser('blabla@gmail.com');
        $this->assertEquals($expected, $actual);
    }
}