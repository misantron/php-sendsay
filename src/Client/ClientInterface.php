<?php

namespace Sendsay\Client;

use Sendsay\Exception\AccessDeniedException;
use Sendsay\Exception\TooManyRedirectsException;
use Sendsay\Message\MessageInterface;

interface ClientInterface
{
    /**
     * @param string $action
     * @param array $data
     * @return MessageInterface
     *
     * @throws AccessDeniedException
     * @throws TooManyRedirectsException
     */
    public function request($action, $data = []);
}