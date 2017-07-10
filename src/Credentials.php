<?php

namespace Sendsay;


class Credentials
{
    protected $login;
    protected $sublogin;
    protected $passwd;

    public function __construct($login, $subLogin, $password)
    {
        $this->login = $login;
        $this->sublogin = $subLogin;
        $this->passwd = $password;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return get_object_vars($this);
    }
}