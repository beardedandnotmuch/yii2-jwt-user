<?php

namespace beardedandnotmuch\user\filters;

use Yii;
use yii\filters\auth\AuthMethod;
use yii\base\InvalidConfigException;

class AuthByToken extends AuthMethod
{
    /**
     * @var string
     */
    const SOURCE_HEADER = 'header';
    const SOURCE_COOKIE = 'cookie';
    const SOURCE_QUERY_PARAM = 'query_param';

    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';

    /**
     * @var string
     */
    public $cookieName = 'token';

    /**
     * @var string
     */
    public $queryParamName = 'token';

    /**
     * @var string[]
     */
    public $sourceOrder = [
        self::SOURCE_HEADER,
        self::SOURCE_COOKIE,
        self::SOURCE_QUERY_PARAM,
    ];

    /**
     * @var array
     */
    private $sources = [
        self::SOURCE_HEADER => 'getTokenFromHeader',
        self::SOURCE_COOKIE => 'getTokenFromCookie',
        self::SOURCE_QUERY_PARAM => 'getTokenFromQuery',
    ];


    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $token = $this->getToken($request);

        if ($token) {
            $identity = $user->loginByAccessToken($token, get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            }

            $identity->setAuthToken($token);

            return $identity;
        }

        return null;
    }

    /**
     * Returns token from the request.
     *
     * @param \yii\web\Request $request
     *
     * @return string|null
     */
    public function getToken($request)
    {
        foreach ($this->sourceOrder as $sourceName) {
            if (!array_key_exists($sourceName, $this->sources)) {
                throw new InvalidConfigException("Source \"$sourceName\" doesn't exists");
            }

            $method = $this->sources[$sourceName];

            if ($token = $this->$method($request)) {
                return $token;
            }
        }
    }

    /**
     * Return token from cookie.
     *
     * @return string|null
     */
    protected function getTokenFromCookie($request)
    {
        $cookie = $request->getCookies()->get($this->cookieName);

        return $cookie ? $cookie->value : null;
    }

    /**
     * Returns token from header.
     *
     * @return string|null
     */
    protected function getTokenFromHeader($request)
    {
        $authHeader = $request->getHeaders()->get('Authorization');

        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Returns token from query.
     *
     * @return string|null
     */
    protected function getTokenFromQuery($request)
    {
        return $request->get($this->queryParamName);
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Bearer realm=\"{$this->realm}\"");
    }
}
