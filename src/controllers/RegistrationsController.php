<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use yii\filters\auth\HttpBearerAuth;

class RegistrationsController extends BaseController
{
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
        $form = Yii::$container->get('beardedandnotmuch\user\models\RegistrationForm');
        $request = Yii::$app->getRequest();
        $security = Yii::$app->getSecurity();

        $form->setAttributes($request->post());

        if (!$form->validate()) {
            return $form;
        }

        $user = $form->register();

        if ($user->hasErrors()) {
            return $user;
        }

        return $user->toArray(['id', 'email']);
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
