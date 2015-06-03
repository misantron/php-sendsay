<?php

namespace Sendsay;

use GuzzleHttp\Client;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Sendsay\Exception\AccessDeniedException;
use Sendsay\Exception\ClientException;
use Sendsay\Exception\TooManyRedirectsException;

class Transport implements TransportInterface
{
    const
        API_VERSION = 100,
        JSON_RESPONSE = 1
    ;

    const REDIRECT_LIMIT = 10;

    const API_BASE_URL = 'https://api.sendsay.ru/';

    /** @var string */
    private $login;
    /** @var string */
    private $password;

    /** @var string */
    private $session;

    /** @var Logger */
    private $logger;
    /** @var Client */
    private $client;

    /** @var array */
    private $options = [
        'log.enabled' => false,
        'log.name' => 'sendsay.api.request',
        'log.path' => 'path/to/your.log',
    ];

    public function __construct($login, $password, $options = [])
    {
        $this->login = $login;
        $this->password = $password;

        if(sizeof($options) > 0){
            $this->options = array_merge($this->options, $options);
        }

        if($this->options['log.enabled']){
            $this->logger = new Logger($this->options['log.name'], [
                new StreamHandler($this->options['log.path'], Logger::WARNING)
            ]);
        }

        $this->client = new Client();
    }

    /**
     * @param string $action
     * @param array $data
     * @return array
     *
     * @throws AccessDeniedException
     * @throws TooManyRedirectsException
     */
    public function sendRequest($action, $data = [])
    {
        $params = [];

        try {
            if ($action !== 'login' && !$this->authorize()){
                throw new AccessDeniedException('');
            }
            $data['session'] = $this->session;

            if($this->options['log.enabled']){
                $this->logger->info('request:' . $action, $data);
            }

            $data['action'] = $action;

            $params = $this->getDefaultRequestParams();
            $params['request'] = json_encode($data);

            $redirectCount = 0;
            $redirectPath = '';

            do {
                $response = $this->request(self::API_BASE_URL . $redirectPath, $params);
                if (isset($response['REDIRECT'])){
                    $redirectPath = $response['REDIRECT'];
                }
                $redirectCount++;
                if ($redirectCount > self::REDIRECT_LIMIT){
                    throw new TooManyRedirectsException('');
                }
            } while (isset($response['REDIRECT']));

            if($this->options['log.enabled']){
                $this->logger->info('response:' . $action, $response ?: []);
            }
        } catch (ClientException $e){
            if($this->options['log.enabled']){
                $this->logger->error($e->getMessage(), $params);
            }
            return false;
        }
        return $response;
    }

    /**
     * @return bool
     */
    private function authorize()
    {
        if (isset($this->session)) {
            return true;
        }

        $response = $this->sendRequest('login', [
            'login' => $this->login,
            'passwd' => $this->password
        ]);

        if(!isset($response['session'])){
            return false;
        }

        $this->session = $response['session'];
        return true;
    }

    /**
     * @return array
     */
    private function getDefaultRequestParams()
    {
        return [
            'apiversion' => self::API_VERSION,
            'json'       => self::JSON_RESPONSE,
            'request.id' => mt_rand(100, 999),
        ];
    }

    /**
     * @param string $url
     * @param array $params
     * @param string $method
     * @return array|null
     *
     * @throws ClientException
     */
    private function request($url, $params = [], $method = 'POST')
    {
        /** @var RequestInterface $response */
        $request = $this->client->createRequest($method, $url, $params);
        /** @var ResponseInterface $response */
        $response = $this->client->send($request);

        $response = $response->json();
        if (isset($response['errors'])) {
            throw new ClientException($response['errors']);
        }
        return $response;
    }
}