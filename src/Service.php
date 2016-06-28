<?php

namespace Sendsay;

use Sendsay\Client\Client;
use Sendsay\Message\MessageInterface;

class Service
{
    /** @var Client */
    private $client;

    public function __construct($credentials, $options = [])
    {
        $this->client = new Client($credentials, $options);
    }

    /**
     * @param array $credentials
     * @param array $options
     * @return Service
     */
    public static function create($credentials, $options = [])
    {
        return new static($credentials, $options);
    }

    /**
     * @param string $email
     * @return array|null
     */
    public function getUser($email)
    {
        /** @var MessageInterface $response */
        $response = $this->client->request('member.get', ['email' => $email]);
        if($response->hasError()){
            return null;
        }
        $data = $response->getData();
        return $data['member'];
    }
}