<?php

namespace Sendsay\Client;

use GuzzleHttp\Client;
use Sendsay\Credentials;
use Sendsay\Exception\TooManyRedirectsException;
use Sendsay\Message\Message;
use Sendsay\Message\MessageInterface;

class Transport implements TransportInterface
{
    const REDIRECT_LIMIT = 10;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    private $defaultRequestParams = [
        'apiversion' => 100,
        'json' => 1,
    ];

    /**
     * @var array
     */
    private $credentials;

    /**
     * @var string
     */
    private $session;

    /**
     * @param Credentials $credentials
     * @param Client $client
     */
    public function __construct(Credentials $credentials, Client $client)
    {
        $this->credentials = $credentials;
        $this->client = $client;

        $this->login();
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function login()
    {
        if (!isset($this->session)) {
            $message = $this->request('login');
            $data = $message->getData();
            if (!isset($data['session'])) {
                throw new \InvalidArgumentException($message->getError());
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
    public function request($action, array $data = null)
    {
        $message = new Message();

        if ($data === null) {
            $data = $this->credentials->getData();
        }

        try {
            $data['action'] = $action;

            $params = $this->buildRequestParams($data);

            $redirectCount = 0;
            $redirectPath = '/';

            do {
                $response = $this->sendRequest($redirectPath, $params);
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

            $message->setData(isset($response['obj']) ? $response['obj'] : $response);
        } catch (\Exception $e){
            $message->setError($e->getMessage());
        }
        return $message;
    }

    /**
     * @param array $response
     * @return string
     */
    private function getErrorMessageFromResponse(array $response)
    {
        $error = reset($response['errors']);
        if (!isset($error['explain'])) {
            return $error['id'];
        }
        return is_string($error['explain']) ? $error['explain'] : serialize($error['explain']);
    }

    /**
     * @param string $url
     * @param array $params
     * @return array|null
     */
    private function sendRequest($url, array $params = [])
    {
        $response = $this->client->post($url, [
            'verify' => false,
            'form_params' => $params
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param array $data
     * @return array
     */
    private function buildRequestParams(array $data)
    {
        if ($this->session !== null && !isset($data['session'])) {
            $data['session'] = $this->session;
        }

        return array_merge(
            $this->defaultRequestParams,
            ['request' => json_encode($data)]
        );
    }
}