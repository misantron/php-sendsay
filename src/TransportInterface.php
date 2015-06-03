<?php

namespace Sendsay;

use Sendsay\Exception\AccessDeniedException;
use Sendsay\Exception\TooManyRedirectsException;

interface TransportInterface
{
    /**
     * @param string $action
     * @param array $data
     * @return array
     *
     * @throws AccessDeniedException
     * @throws TooManyRedirectsException
     */
    public function sendRequest($action, $data = []);
}