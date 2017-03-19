<?php

namespace beardedandnotmuch\user\controllers;

use yii\rest\Controller as BaseController;

class TokenController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        // we don't needs any predefined behaviors of this controller.
        return [
            'beardedandnotmuch\user\filters\NgTokenAuth',
            'beardedandnotmuch\user\filters\UpdateAuthHeaders',
            'yii\filters\RateLimiter',
        ];
    }

    /**
     * Validate user's token.
     *
     * @return array
     * @throw \yii\web\ForbiddenHttpException
     */
    public function actionValidate()
    {
        /*
         * @var yii\web\IdentityInterface
         */
        $identity = \Yii::$app->getUser()->getIdentity();

        // NOTE: at this point we already validate user's token
        // and authenticate them
        // all that's left to do is send correct response
        if (!$identity) {
            throw new \yii\web\ForbiddenHttpException('Access denied');
        }

        $extra = array_merge(['role'],
            $identity->isAdmin() ? ['configs'] : []
        );

        return $identity->profile->toArray([], $extra);
    }
}
