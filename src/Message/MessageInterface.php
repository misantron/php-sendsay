<?php

namespace Sendsay\Message;

interface MessageInterface
{
    /**
     * @return string|null
     */
    public function getError();

    /**
     * @param string $value
     * @return $this
     */
    public function setError($value);

    /**
     * @return bool
     */
    public function hasError();

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @param mixed $value
     * @return $this
     */
    public function setData($value);
}