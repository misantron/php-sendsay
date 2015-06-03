<?php

namespace Sendsay\Tests;

use Sendsay\Client;

class ClientTest extends BaseTestCase
{
    /** @var Client */
    private $client;

    protected function setUp()
    {
        parent::setUp();

        $login = '';
        $password = '';
        $options = ['log.enabled' => false];

        $this->client = new Client($login, $password, $options);
    }

    public function testGetUser()
    {
        $expected = [];
        $actual = $this->client->getUser('');
        $this->assertEquals($expected, $actual);
    }
}