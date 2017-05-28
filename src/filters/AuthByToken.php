<?php

namespace beardedandnotmuch\user\filters;

use Yii;
use yii\filters\auth\AuthMethod;
use beardedandnotmuch\user\helpers\JWT;
use yii\web\Cookie;

class AuthByToken extends AuthMethod
{
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
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $token = $this->getTokenFromHeader($request);

        if (!$token) {
            $token = $this->getTokenFromCookie($request);
        }

        if (!$token) {
            $token = $this->getTokenFromQuery($request);
        }

        if ($token) {
            $identity = $user->loginByAccessToken($token, get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            }

            return $identity;
        }

        return null;
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
