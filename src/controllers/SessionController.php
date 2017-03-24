<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use beardedandnotmuch\user\traits\ModuleTrait;
use yii\filters\auth\HttpBearerAuth;
use beardedandnotmuch\user\helpers\JWT;

class SessionController extends BaseController
{
    use ModuleTrait;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        // we don't needs any predefined behaviors of this controller.
        return array_merge(parent::behaviors(), [
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'only' => ['delete'],
            ],
        ]);
    }

    /**
     * Create new user's session.
     *
     * @return array
     * @throw UnauthorizedHttpException
     */
    public function actionCreate()
    {
        $model = Yii::createObject($this->module->modelMap['LoginForm']);
        $request = Yii::$app->getRequest();

        if ($model->load($request->post()) && $model->login()) {
            $user = $model->getUser();

            return ['token' => JWT::token($user)];
        }

        return $model;
    }

    /**
     * Destroy user's session.
     *
     * @return bool
     * @throw yii\web\NotFoundHttpException
     */
    public function actionDelete()
    {
        $user = Yii::$app->getUser();
        /*
         * @var yii\web\IdentityInterface
         */
        $identity = $user->getIdentity();

        if (!$identity) {
            throw new NotFoundHttpException('User was not found or was not logged in');
        }

        return $user->logout();
    }
}
