<?php

namespace Sendsay\Client;

use Sendsay\Exception\TooManyRedirectsException;
use Sendsay\Message\MessageInterface;

interface ClientInterface
{
    /**
     * @param string $action
     * @param array $data
     * @return MessageInterface
     *
     * @throws TooManyRedirectsException
     */
    public function request($action, $data = []);
}