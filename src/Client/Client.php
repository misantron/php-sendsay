<?php

namespace Sendsay\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Message\ResponseInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Sendsay\Exception\ClientException;
use Sendsay\Exception\TooManyRedirectsException;
use Sendsay\Message\Message;
use Sendsay\Message\MessageInterface;

class Client implements ClientInterface
{
    const
        API_VERSION    = 100,
        JSON_RESPONSE  = 1,
        REDIRECT_LIMIT = 10
    ;

    /** @var string */
    private $baseUrl = 'https://api.sendsay.ru/';
    /** @var string */
    private $session;

    /** @var Logger */
    private $logger;
    /** @var HttpClient */
    private $httpClient;

    /** @var array */
    private $options = [
        'login' => null,
        'password' => null,
        'log' => [
            'enabled' => false,
            'file.name' => 'sendsay.api.request',
            'file.path' => null,
        ]
    ];

    public function __construct($options)
    {
        $this->options = array_merge($this->options, $options);

        $this->client = new HttpClient();

        if($this->options['log']['enabled']){
            $this->logger = new Logger($this->options['log']['file.name'], [
                new StreamHandler($this->options['log']['file.path'], Logger::INFO)
            ]);
        }

        $this->init();
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function init()
    {
        if (!isset($this->session)) {
            $response = $this->request('login', [
                'login' => $this->options['login'],
                'passwd' => $this->options['password']
            ]);
            if(!isset($response['session'])){
                throw new \InvalidArgumentException('Api login or password is invalid');
            }
            $this->session = $response['session'];
        }
    }

    /**
     * @param string $action
     * @param array $data
     * @return MessageInterface
     *
     * @throws TooManyRedirectsException
     */
    public function request($action, $data = [])
    {
        $params = [];

        $message = new Message();

        try {
            $this->log('info', 'request:' . $action, $data);

            $data['action'] = $action;

            $params = $this->buildRequestParams($data);

            $redirectCount = 0;
            $redirectPath = '';

            do {
                $response = $this->sendRequest($this->baseUrl . $redirectPath, $params);
                if (isset($response['REDIRECT'])){
                    $redirectPath = $response['REDIRECT'];
                }
                $redirectCount++;
                if ($redirectCount > self::REDIRECT_LIMIT){
                    throw new TooManyRedirectsException('Too many redirects');
                }
            } while (isset($response['REDIRECT']));

            $this->log('info', 'response:' . $action, $response ?: []);

            return $message->setData($response);
        } catch (ClientException $e){
            $this->log('error', 'error:' . $e->getMessage(), $params);

            return $message->setError($e->getMessage());
        }
    }

    /**
     * @param string $url
     * @param array $params
     * @return array|null
     *
     * @throws ClientException
     */
    private function sendRequest($url, $params = [])
    {
        /** @var ResponseInterface $response */
        $response = $this->httpClient->post($url, $params);

        $data = $response->json();
        if (isset($data['errors'])) {
            throw new ClientException($data['errors']);
        }
        return $data;
    }

    /**
     * @param string $method
     * @param string $message
     * @param array $data
     */
    private function log($method, $message, $data)
    {
        if(!($this->logger instanceof Logger)){
            return;
        }
        $this->logger->$method($message, $data);
    }

    /**
     * @param array $data
     * @return array
     */
    private function buildRequestParams($data)
    {
        return [
            'apiversion' => self::API_VERSION,
            'json'       => self::JSON_RESPONSE,
            'request.id' => mt_rand(100, 999),
            'session'    => $this->session,
            'request'    => $this->encodeRequestData($data),
        ];
    }

    /**
     * @param array $data
     * @return string
     */
    private function encodeRequestData($data)
    {
        array_walk($data, function($item, $key){
            if(!mb_detect_encoding($item, 'utf-8', true)){
                $data[$key] = utf8_encode($item);
            }
        });
        return json_encode($data);
    }
}