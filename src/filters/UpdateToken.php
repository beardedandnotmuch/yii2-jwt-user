<?php

namespace beardedandnotmuch\user\filters;

use Yii;
use yii\base\ActionFilter as BaseFilter;
use beardedandnotmuch\user\helpers\JWT;
use yii\web\Cookie;
use yii\web\Response;

class UpdateToken extends BaseFilter
{
    public $duration = 24 * 60 * 60;

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        $user = Yii::$app->getUser();
        if ($user->getIsGuest()) {
            return $result;
        }

        if (is_array($result)) {
            $token = JWT::token($user->getIdentity(), $this->duration);

            return array_merge($result, [
                'token' => (string) $token,
            ]);
        }

        return $result;
    }
}
