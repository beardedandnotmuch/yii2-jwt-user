<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use beardedandnotmuch\user\filters\AuthByToken;
use beardedandnotmuch\user\filters\UpdateToken;
use beardedandnotmuch\user\events\AfterRegistrationEvent;
use beardedandnotmuch\user\Module;

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
                'useCookie' => $this->module->useCookie,
                'duration' => $this->module->duration,
                'only' => ['create'],
            ];
        }

        return array_merge($behaviors, [
            'authenticator' => [
                'class' => AuthByToken::class,
                'optional' => ['create'],
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
        if (!Yii::$app->getUser()->getIsGuest()) {
            throw new \yii\web\BadRequestHttpException();
        }

        $this->module->trigger(Module::EVENT_BEFORE_REGISTER);

        $request = Yii::$app->getRequest();
        $form = Yii::$container->get('beardedandnotmuch\user\models\RegistrationForm');

        $form->setAttributes($request->post());

        if (!$form->register()) {
            return $form;
        }

        if ($this->module->forceLogin) {
            Yii::$app->getUser()->login($form->getUser());
        }

        $this->module->trigger(Module::EVENT_AFTER_REGISTER, Yii::createObject([
            'class' => AfterRegistrationEvent::class,
            'form' => $form,
        ]));

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
