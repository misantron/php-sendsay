<?php

namespace Sendsay;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Logger;
use Sendsay\Client\Transport;

class Service
{
    const ENDPOINT = 'https://api.sendsay.ru/';

    /** @var Transport */
    private $transport;

    /**
     * @param string $login
     * @param string $password
     * @param string|null $subLogin
     * @param array $options
     */
    public function __construct($login, $password, $subLogin = null, array $options = [])
    {
        $credentials = new Credentials($login, $subLogin, $password);

        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                new Logger($options['log.name']),
                new MessageFormatter(isset($options['log.format']) ? $options['log.format'] : null)
            )
        );

        $client = new Client([
            'base_uri' => self::ENDPOINT,
            'handler' => $stack
        ]);

        $this->transport = new Transport($credentials, $client);
    }

    /**
     * @param string $login
     * @param string $password
     * @param string|null $subLogin
     * @param array $options
     * @return Service
     */
    public static function create($login, $password, $subLogin = null, array $options = [])
    {
        return new static($login, $password, $subLogin, $options);
    }

    /**
     * @param string $email
     * @return array|null
     */
    public function getUser($email)
    {
        $response = $this->transport->request('member.get', ['email' => $email]);
        if ($response->hasError()) {
            return null;
        }
        $data = $response->getData();
        return $data['member'];
    }
}