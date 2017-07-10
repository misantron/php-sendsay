<?php

namespace Sendsay\Client;

use Sendsay\Exception\TooManyRedirectsException;
use Sendsay\Message\MessageInterface;

interface TransportInterface
{
    /**
     * @param string $action
     * @param array $data
     * @return MessageInterface
     *
     * @throws TooManyRedirectsException
     */
    public function request($action, array $data = []);
}