<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use beardedandnotmuch\user\models\User;
use yii\rest\Controller as BaseController;
use yii\web\Response;

/**
 * Provides authentication logic.
 *
 * @see yii\rest\Controller
 */
class AuthController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        // we don't needs any predefined behaviors of this controller.
        return [
            'yii\filters\RateLimiter',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'sign_out' => [
                'class' => 'beardedandnotmuch\user\actions\SignOutAction',
            ],
            'validate_token' => [
                'class' => 'beardedandnotmuch\user\actions\ValidateTokenAction',
            ],
        ];
    }
}
