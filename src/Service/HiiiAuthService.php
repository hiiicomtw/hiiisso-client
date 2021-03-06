<?php

namespace Hiiicomtw\HiiiSSOClient\Service;

use GuzzleHttp\Client;
use Hiiicomtw\HiiiSSOClient\Exceptions\InvalidStateException;
use Hiiicomtw\HiiiSSOClient\Helper\DataHelper;
use Hiiicomtw\HiiiSSOClient\Model\Admin;
use Hiiicomtw\HiiiSSOClient\Model\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class HiiiAuthService
{
    protected $request;
    protected $httpClient;
    protected $serverUrl;
    protected $clientId;
    protected $clientSecret;
    protected $redirectUrl;
    protected $failRedirectUrl;
    protected $parameters = [];
    protected $guzzle = [];
    protected $scopes = [];
    protected $scopeSeparator = ',';
    protected $encodingType = PHP_QUERY_RFC1738;
    public $guard = 'admin';



    public function setConfig()
    {
        $this->guzzle = Arr::get(config('hiiisso-client'), 'guzzle', []);
        $this->clientId = config('hiiisso-client.client_id');
        $this->clientSecret = config('hiiisso-client.client_secret');
        $this->serverUrl = config('hiiisso-client.server_url');
        $this->redirectUrl = $this->formatRedirectUrl(config('hiiisso-client.redirect'));
        if(!DataHelper::keyValueIsEmpty('fail_redirect', config('hiiisso-client'))){
            $this->failRedirectUrl = $this->formatRedirectUrl(config('hiiisso-client.fail_redirect'));
        }
    }

    public function guard($guard)
    {
        $this->guard = $guard;
        return $this;
    }

    public function getGuard()
    {
        return $this->guard;
    }

    public function with(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    protected function getState()
    {
        return Str::random(40);
    }

    /**
     * Get a instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client($this->guzzle);
        }

        return $this->httpClient;
    }

    /**
     * Set the Guzzle HTTP client instance.
     *
     * @param  \GuzzleHttp\Client  $client
     * @return $this
     */
    public function setHttpClient(Client $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->serverUrl . '/api/sso/' . $this->getGuard() . '/authorize', $state);
    }

    protected function buildAuthUrlFromBase($url, $state)
    {
        return $url.'?'.http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);
    }

    public function getScopes()
    {
        return $this->scopes;
    }

    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id' => $this->clientId,
            'redirect_url' => $this->redirectUrl,
            'fail_redirect_url' => $this->failRedirectUrl,
            'scope' => $this->formatScopes($this->getScopes(), $this->scopeSeparator),
            'response_type' => 'code',
            'guard' => $this->guard,
            'state' => $state
        ];

        return array_merge($fields, $this->parameters);
    }

    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
    }

    public function getTokenUrl()
    {
        return $this->serverUrl . '/api/sso/' . $this->getGuard() . '/token';
    }

    public function redirectUrl($url)
    {
        $this->redirectUrl = $url;
        return $this;
    }

    /**
     * @param $redirect
     *
     * @return mixed
     */
    protected function formatRedirectUrl($redirect)
    {
        return Str::startsWith($redirect, '/')
            ? $this->app['url']->to($redirect)
            : $redirect;
    }

    /**
     * @param $token
     *
     * @return mixed
     */
    protected function getUserByToken($token)
    {
        $userUrl = $this->serverUrl . '/api/sso/' . $this->getGuard() . '/user?access_token='.$token;
        $response = $this->getHttpClient()->get($userUrl);

        $user = json_decode($response->getBody(), true);

        return $user;
    }

    /**
     * @return Admin|Customer
     */
    public function user()
    {
        if ($this->hasInvalidState()) {
//            error_log("has invalid state");
//            throw new InvalidStateException('invalid state');
        }
        $response = $this->getAccessTokenResponse($this->getCode());
        $user = $this->mapUserToObject($this->getUserByToken(
            $token = Arr::get($response, 'access_token')
        ));

        return $user->setToken($token)
                    ->setRefreshToken(Arr::get($response, 'refresh_token'))
                    ->setExpiresIn(Arr::get($response, 'expires_in'));
    }

    /**
     * @param $token
     *
     * @return Admin|Customer
     */
    public function userFromToken($token)
    {
        $user = $this->mapUserToObject($this->getUserByToken($token));

        return $user->setToken($token);
    }

    /**
     * @param array $user
     *
     * @return Admin|Customer
     */
    protected function mapUserToObject(array $user)
    {
        if($this->guard == 'admin') {
            return (new Admin)->setRaw($user)->map([
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'username' => $user['username'],
            ]);
        }else {
            return (new Customer)->setRaw($user)->map([
                'id' => $user['id'],
                'groupId' => $user['customer_group_id'],
                'groupTitle' => $user['customer_group_title'],
                'name' => $user['name'],
                'email' => $user['email'],
                'cellphone' => $user['cellphone'],
                'profile' => $user['profile'],
            ]);
        }

    }

    protected function getCode()
    {
        return $this->request->input('code');
    }

    public function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
            'fail_redirect_uri' => $this->failRedirectUrl,
            'guard' => $this->guard
        ];
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    public function getAccessTokenResponse($code)
    {

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $this->getTokenFields($code)
        ]);

        return json_decode($response->getBody(), true);
    }

    public function redirect()
    {
        $state = null;
        $this->request->session()->put('state', $state = $this->getState());
        return new RedirectResponse($this->getAuthUrl($state));
    }

    protected function hasInvalidState()
    {
        $state = $this->request->session()->pull('state');
        return ! (strlen($state) > 0 && $this->request->input('state') === $state);
    }
}
