<?php

namespace Sendsay\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
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

    /** @var HttpClient */
    protected $httpClient;

    /** @var string */
    protected $baseUrl = 'https://api.sendsay.ru/';
    /** @var string */
    protected $logger = 'api.sendsay';

    /** @var array */
    protected $options = [
        'login' => null,
        'password' => null,
        'log.file.path' => null,
    ];

    /** @var string */
    private $session;

    public function __construct($options)
    {
        $this->options = array_merge($this->options, $options);

        $logger = new Logger($this->logger, [
            new StreamHandler($this->options['log.file.path'], Logger::INFO)
        ]);
        $subscriber = new LogSubscriber($logger);
        $this->httpClient = new HttpClient();
        $this->httpClient->getEmitter()->attach($subscriber);

        $this->init();
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function init()
    {
        if (!isset($this->session)) {
            /** @var MessageInterface $message */
            $message = $this->request('login', [
                'login' => $this->options['login'],
                'passwd' => $this->options['password']
            ]);
            $data = $message->getData();
            if(!isset($data['session'])){
                throw new \InvalidArgumentException('Api login or password is invalid');
            }
            $this->session = $data['session'];
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
        $message = new Message();

        try {
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

            if (isset($response['errors'])) {
                $errorMessage = $this->getErrorMessageFromResponse($response);
                return $message->setError($errorMessage);
            }

            return $message->setData(isset($response['obj']) ? $response['obj'] : $response);
        } catch (\Exception $e){
            return $message->setError($e->getMessage());
        }
    }

    /**
     * @param array $response
     * @return string
     */
    private function getErrorMessageFromResponse($response)
    {
        $error = reset($response['errors']);
        if(!isset($error['explain'])){
            return $error['id'];
        }
        return is_string($error['explain']) ? $error['explain'] : serialize($error['explain']);
    }

    /**
     * @param string $url
     * @param array $params
     * @return array|null
     */
    private function sendRequest($url, $params = [])
    {
        /** @var ResponseInterface $response */
        $response = $this->httpClient->post($url, [
            'verify' => false,
            'body' => $params
        ]);

        return $response->json();
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
            'request'    => $this->encodeRequestData($data),
        ];
    }

    /**
     * @param array $data
     * @return string
     */
    private function encodeRequestData($data)
    {
        if($this->session !== null){
            $data['session'] = $this->session;
        }
        array_walk($data, function($item, $key){
            if(!mb_detect_encoding($item, 'utf-8', true)){
                $data[$key] = utf8_encode($item);
            }
        });
        return json_encode($data);
    }
}