<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use beardedandnotmuch\user\traits\ModuleTrait;
use yii\filters\auth\HttpBearerAuth;

class RegistrationsController extends BaseController
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
                'only' => ['update', 'delete'],
            ],
        ]);
    }

    /**
     * Create new account with email provider.
     *
     * @return \yii\web\Response
     * @throw \yii\web\BadRequestHttpException
     */
    public function actionCreate()
    {
        $model = Yii::createObject($this->module->modelMap['RegistrationForm']);
        $request = Yii::$app->getRequest();
        $security = Yii::$app->getSecurity();

        $model->load($request->post());
        if ($user = $model->register()) {
            return $user->toArray(['id', 'email']);
        }

        return $model;
    }

    /**
     * Update exist account.
     */
    public function actionUpdate()
    {
        return null;
    }

    /**
     * Destroy exist account.
     *
     * @throw \yii\web\UnauthorizedHttpException
     * @throw \Exception
     */
    public function actionDelete()
    {
        return null;
    }
}
