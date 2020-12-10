<?php


namespace Hiiicomtw\HiiiSSOClient\Service;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Hiiicomtw\HiiiSSOClient\Helper\DataHelper;
use Hiiicomtw\HiiiSSOClient\Model\Admin;
use Hiiicomtw\HiiiSSOClient\Model\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Hiiicomtw\HiiiSSOClient\Exceptions\InvalidStateException;

abstract class AbstractProvider implements ProviderContract
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
    protected $stateless = false;

    public function __construct(Request $request, $config = [])
    {
        $this->request = $request;
        $this->guzzle = Arr::get($config, 'guzzle', []);
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->serverUrl = $config['server_url'];
        $this->redirectUrl = $config['redirect'];
        $this->failRedirectUrl = $config['fail_redirect'];
    }


    /**
     * Get the authentication URL for the provider.
     *
     * @param  string  $state
     * @return string
     */
    abstract protected function getAuthUrl($state);

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    abstract protected function getTokenUrl();

    /**
     * Get the raw user for the given access token.
     *
     * @param  string  $token
     * @return array
     */
    abstract protected function getUserByToken($token);

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
     * @return Admin|Customer
     */
    abstract protected function mapUserToObject(array $user);

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        $state = null;

        if ($this->usesState()) {
            $this->request->session()->put('state', $state = $this->getState());
        }

        return new RedirectResponse($this->getAuthUrl($state));
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string  $url
     * @param  string  $state
     * @return string
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        return $url.'?'.http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param  string|null  $state
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id' => $this->clientId,
            'redirect_url' => $this->redirectUrl,
            'fail_redirect_url' => $this->failRedirectUrl,
            'scope' => $this->formatScopes($this->getScopes(), $this->scopeSeparator),
            'response_type' => 'code',
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return array_merge($fields, $this->parameters);
    }

    /**
     * Format the given scopes.
     *
     * @param  array  $scopes
     * @param  string  $scopeSeparator
     * @return string
     */
    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
//        if ($this->hasInvalidState()) {
//            throw new InvalidStateException;
//        }

        $response = $this->getAccessTokenResponse($this->getCode());
        $user = $this->mapUserToObject($this->getUserByToken(
            $token = Arr::get($response, 'access_token')
        ));

        return $user->setToken($token)
                    ->setRefreshToken(Arr::get($response, 'refresh_token'))
                    ->setExpiresIn(Arr::get($response, 'expires_in'));
    }

    /**
     * Get a Social User instance from a known access token.
     *
     * @param  string  $token
     * @return \Laravel\Socialite\Two\User
     */
    public function userFromToken($token)
    {
        $user = $this->mapUserToObject($this->getUserByToken($token));

        return $user->setToken($token);
    }

    /**
     * Determine if the current request / session has a mismatching "state".
     *
     * @return bool
     */
    protected function hasInvalidState()
    {
        if ($this->isStateless()) {
            return false;
        }

        $state = $this->request->session()->pull('state');

        return ! (strlen($state) > 0 && $this->request->input('state') === $state);
    }

    /**
     * Get the access token response for the given code.
     *
     * @param  string  $code
     * @return array
     */
    public function getAccessTokenResponse($code)
    {
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            $postKey => $this->getTokenFields($code),
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
            'fail_redirect_uri' => $this->failRedirectUrl,
        ];
    }

    /**
     * Get the code from the request.
     *
     * @return string
     */
    protected function getCode()
    {
        return $this->request->input('code');
    }

    /**
     * Merge the scopes of the requested access.
     *
     * @param  array|string  $scopes
     *
     * @return AbstractProvider
     */
    public function scopes($scopes)
    {
        $this->scopes = array_unique(array_merge($this->scopes, (array) $scopes));

        return $this;
    }

    /**
     * Set the scopes of the requested access.
     *
     * @param  array|string  $scopes
     * @return $this
     */
    public function setScopes($scopes)
    {
        $this->scopes = array_unique((array) $scopes);

        return $this;
    }

    /**
     * Get the current scopes.
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Set the redirect URL.
     *
     * @param  string  $url
     * @return $this
     */
    public function redirectUrl($url)
    {
        $this->redirectUrl = $url;

        return $this;
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

    /**
     * Set the request instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Determine if the provider is operating with state.
     *
     * @return bool
     */
    protected function usesState()
    {
        return ! $this->stateless;
    }

    /**
     * Determine if the provider is operating as stateless.
     *
     * @return bool
     */
    protected function isStateless()
    {
        return $this->stateless;
    }

    /**
     * Indicates that the provider should operate as stateless.
     *
     * @return $this
     */
    public function stateless()
    {
        $this->stateless = true;

        return $this;
    }

    /**
     * Get the string used for session state.
     *
     * @return string
     */
    protected function getState()
    {
        return Str::random(40);
    }

    /**
     * Set the custom parameters of the request.
     *
     * @param  array  $parameters
     * @return $this
     */
    public function with(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}
