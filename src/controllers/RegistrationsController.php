<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use yii\filters\auth\HttpBearerAuth;
use beardedandnotmuch\user\filters\UpdateToken;

class RegistrationsController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        if ($this->module->forceLogin) {
            $behaviors['updatetoken'] = [
                'class' => UpdateToken::class,
                'only' => ['create'],
            ];
        }

        return array_merge($behaviors, [
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
        $request = Yii::$app->getRequest();
        $form = Yii::$container->get('beardedandnotmuch\user\models\RegistrationForm');

        $form->setAttributes($request->post());

        if (!$form->register()) {
            return $form;
        }

        if ($this->module->forceLogin) {
            Yii::$app->getUser()->login($form->getUser());
        }

        return $form->toArray();
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
