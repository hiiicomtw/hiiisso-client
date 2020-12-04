<?php


namespace Hiiicomtw\HiiiSSOClient\Model;


class Admin extends AbstractUser
{
    public $username;
    public $token;
    public $refreshToken;
    public $expiresIn;

    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }
}