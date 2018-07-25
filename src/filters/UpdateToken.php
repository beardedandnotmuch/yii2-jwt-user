<?php

namespace beardedandnotmuch\user\filters;

use Yii;
use yii\base\ActionFilter as BaseFilter;
use beardedandnotmuch\user\helpers\JWT;
use yii\web\Cookie;

class UpdateToken extends BaseFilter
{
    /**
     * @var string
     */
    public $cookieName = 'token';

    /**
     * @var string
     */
    public $headerName = 'X-Set-Token';

    /**
     * @var integer
     */
    public $duration = 24 * 60 * 60;

    /**
     * @var bool
     */
    public $useCookie = false;

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        $user = Yii::$app->getUser();
        if ($user->getIsGuest()) {
            return $result;
        }

        $identity = $user->getIdentity();
        $token = JWT::token($identity, $this->duration);
        $identity->setAuthToken((string) $token);

        if ($result instanceof Response) {
            $this->injectToken($result, $token);
        } else {
            $this->injectToken(Yii::$app->getResponse(), $token);
        }

        return $result;
    }

    /**
     * @param Response $response
     * @param Lcobucci\JWT\Token $token
     */
    public function injectToken($response, $token)
    {
        if ($this->useCookie) {
            $cookie = new Cookie();
            $cookie->name = $this->cookieName;
            $cookie->value = (string) $token;
            $cookie->expire = time() + (int) $this->duration;
            $cookie->httpOnly = false;
            $response->getCookies()->add($cookie);
        } else {
            $response->getHeaders()->set($this->headerName, (string) $token);
        }
    }

}
