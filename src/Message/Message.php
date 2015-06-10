<?php

namespace Sendsay\Message;

class Message implements MessageInterface
{
    /** @var string */
    private $error;
    /** @var array */
    private $data;

    /**
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setError($value)
    {
        $this->error = (string)$value;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->error !== null;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setData($value)
    {
        $this->data = (array)$value;
        return $this;
    }
}