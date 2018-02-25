<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use beardedandnotmuch\user\filters\UpdateToken;
use beardedandnotmuch\user\filters\AuthByToken;
use beardedandnotmuch\user\Module;
use beardedandnotmuch\user\events\SendResetPasswordEvent;
use yii\helpers\Url;

class PasswordController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'authenticator' => [
                'class' => AuthByToken::class,
                'only' => ['update'],
            ],
            'updatetoken' => [
                'class' => UpdateToken::class,
                'useCookie' => $this->module->useCookie,
                'duration' => $this->module->duration,
                'only' => ['update'],
            ],
        ]);
    }

    /**
     * Update password of the authenticated user
     *
     * @throw BadRequestHttpException
     * @return array
     */
    public function actionUpdate()
    {
        $form = Yii::$container->get('beardedandnotmuch\user\models\PasswordForm');
        $form->setAttributes($this->request->post());

        if (!$form->validate()) {
            return $form;
        }

        $user = $this->user->getIdentity();

        return ['success' => $user->setPassword($form->new_password)->save(false)];
    }

    /**
     * Set new password to the user after he clicks confirm url in the email.
     *
     * @return array
     */
    public function actionReplace()
    {
        $form = Yii::$container->get('beardedandnotmuch\user\models\ReplacePasswordForm');
        $form->setAttributes($this->request->post());

        if (!$form->validate()) {
            return $form;
        }

        $user = $form->getUser();

        return ['success' => $user->setPassword($form->password)->save(false)];

    }

    /**
     * An anonymous user send request to reset password.
     *
     * @return array
     */
    public function actionReset()
    {
        $form = Yii::$container->get('beardedandnotmuch\user\models\ResetPasswordForm');
        $form->setAttributes($this->request->post());

        if (!$form->validate()) {
            return $form;
        }

        $this->module->trigger(Module::EVENT_SEND_RESET_PASSWORD, Yii::createObject([
            'class' => SendResetPasswordEvent::class,
            'form' => $form,
            'mailer' => $this->mailer,
        ]));

        return ['success' => true];
    }

}
