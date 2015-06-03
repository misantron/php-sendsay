<?php

namespace Sendsay;

class Client
{
    /** @var Transport */
    private $transport;

    public function __construct($login, $password, $options = [])
    {
        $this->transport = new Transport($login, $password, $options);
    }

    /**
     * @param string $login
     * @param string $password
     * @param array $options
     * @return Client
     */
    public static function create($login, $password, $options = [])
    {
        return new static($login, $password, $options);
    }

    /**
     * @param string $email
     * @return array
     */
    public function getUser($email)
    {
        return $this->transport->sendRequest('member.get', ['email' => $email]);
    }
}