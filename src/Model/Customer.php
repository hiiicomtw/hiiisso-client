<?php


namespace Hiiicomtw\HiiiSSOClient\Model;


class Customer extends AbstractUser
{
    public $cellphone;
    public $groupId;
    public $groupTitle;
    public $token;
    public $refreshToken;
    public $expiresIn;

    public function getGroupId()
    {
        return $this->groupId;
    }

    public function getGroupTitle()
    {
        return $this->groupTitle;
    }

    public function getCellphone()
    {
        return $this->cellphone;
    }

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