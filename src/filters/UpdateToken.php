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

        $token = JWT::token($user->getIdentity(), $this->duration);

        if ($this->useCookie) {
            $cookie = new Cookie();
            $cookie->name = $this->cookieName;
            $cookie->value = (string) $token;
            $cookie->expire = time() + (int) $this->duration;
            $cookie->httpOnly = false;
            Yii::$app->getResponse()->getCookies()->add($cookie);
        } elseif (is_array($result)) {

            return array_merge($result, [
                'token' => (string) $token,
            ]);
        }

        return $result;
    }
}
