<?php


namespace Hiiicomtw\HiiiSSOClient\Service;

use Hiiicomtw\HiiiSSOClient\Model\Admin;
use Hiiicomtw\HiiiSSOClient\Model\Customer;
use Illuminate\Support\Arr;
use GuzzleHttp\ClientInterface;

class AdminProvider extends AbstractProvider implements ProviderInterface
{
    public $guard = 'admin';

    public function guard($guard)
    {
        $this->guard = $guard;
        return $this;
    }

    public function getGuard()
    {
        return $this->guard;
    }

    public function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->serverUrl . '/api/sso/' . $this->getGuard() . '/authorize', $state);
    }

    public function getTokenUrl()
    {
        return $this->serverUrl . '/api/sso/' . $this->getGuard() . '/token';
    }

    protected function getUserByToken($token)
    {
        $userUrl = $this->serverUrl . '/api/sso/' . $this->getGuard() . '/user';
        $response = $this->getHttpClient()->get($userUrl, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token
            ],
        ]);

        $user = json_decode($response->getBody(), true);

        return $user;
    }

    protected function mapUserToObject(array $user)
    {
        return (new Admin)->setRaw($user)->map([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'username' => $user['username'],
        ]);

    }

}
