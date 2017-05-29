<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use beardedandnotmuch\user\filters\UpdateToken;
use beardedandnotmuch\user\filters\AuthByToken;
use yii\helpers\Url;
use League\Uri\Schemes\Http;
use League\Uri\Modifiers\MergeQuery;

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
        $form->setAttributes(Yii::$app->getRequest()->post());

        if (!$form->validate()) {
            return $form;
        }

        $user = Yii::$app->getUser()->getIdentity();

        return ['success' => $user->setPassword($form->new_password)->save(false)];
    }

    /**
     * Set new password to the user after he clicks confirm url in the email.
     *
     * @return void
     */
    public function actionReplace()
    {
        $form = Yii::$container->get('beardedandnotmuch\user\models\ReplacePasswordForm');
        $form->setAttributes(Yii::$app->getRequest()->post());

        if (!$form->validate()) {
            return $form;
        }

        $user = $form->getUser();

        return ['success' => $user->setPassword($form->password)->save(false)];

    }

    /**
     * An anonymous user send request to reset password.
     *
     * @return void
     */
    public function actionReset()
    {
        $form = Yii::$container->get('beardedandnotmuch\user\models\ResetPasswordForm');
        $form->setAttributes(Yii::$app->getRequest()->post());

        if (!$form->validate()) {
            return $form;
        }

        return ['success' => $this->sendResetPasswordInstruction($form)];
    }

    /**
     * Send email with reset password instruction to the user.
     *
     * @return bool
     */
    protected function sendResetPasswordInstruction($form)
    {
        $uri = Http::createFromString($form->redirect_url);
        $modifier = new MergeQuery("token={$form->createToken()}");

        $params = [
            'url' => (string) $modifier->process($uri),
            'email' => $form->email,
            'text' => 'Someone has requested a link to change your password. You can do this through the link below',
        ];

        $mailer = Yii::$app->getMailer();
        $mailer->setViewPath('@beardedandnotmuch/user/views/mail');

        return $mailer->compose('auth/reset_password', $params)
            ->setFrom(Yii::$app->params['adminEmail'])
            ->setTo($form->email)
            ->setSubject('Reset password instructions')
            ->send();
    }

}
