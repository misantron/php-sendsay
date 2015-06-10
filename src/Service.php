<?php

namespace Sendsay;

use Sendsay\Client\Client;
use Sendsay\Message\MessageInterface;

class Service
{
    /** @var Client */
    private $client;

    public function __construct($options)
    {
        $this->client = new Client($options);
    }

    /**
     * @param array $options
     * @return Service
     */
    public static function create($options)
    {
        return new static($options);
    }

    /**
     * @param string $email
     * @return array|null
     */
    public function getUser($email)
    {
        /** @var MessageInterface $response */
        $response = $this->client->request('member.get', ['email' => $email]);
        return $response->hasError() ? null : $response->getData();
    }
}