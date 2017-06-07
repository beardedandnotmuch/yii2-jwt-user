<?php

namespace beardedandnotmuch\user\models;

use Yii;

class RegistrationForm extends BaseRegistrationForm
{
    public $confirm_success_url;

    protected $confirm_token;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $class = Yii::$app->getUser()->identityClass;

        return [
            // email rules
            'emailTrim'     => ['email', 'filter', 'filter' => 'trim'],
            'emailRequired' => ['email', 'required'],
            'emailPattern'  => ['email', 'email'],
            'emailUnique'   => [
                'email',
                'unique',
                'targetClass' => $class,
                'message' => Yii::t('app', 'This email address has already been taken')
            ],
            // password rules
            'passwordRequired' => ['password', 'required'],
            'passwordLength'   => ['password', 'string', 'min' => 6, 'max' => 72],

            // confirm_success_url
            'confirmSuccessUrl' => ['confirm_success_url', 'url'],
            'confirmSuccessUrlRequired' => ['confirm_success_url', 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function loadAttributes(User $user)
    {
        $user->setAttributes($this->attributes);
        $user->setPassword($this->password);
        $user->setConfirmToken($this->generateConfirmToken());
    }

    /**
     * undocumented function
     *
     * @return string
     */
    protected function generateConfirmToken()
    {
        if (empty($this->confirm_token)) {
            $this->confirm_token = Yii::$app->getSecurity()->generateRandomString();
        }

        return $this->confirm_token;
    }

    /**
     * Send confirmation to email.
     */
    protected function sendConfirmation(User $user)
    {
        $query = [
            'auth/confirmation',
            'token' => $this->confirm_token,
            'redirect_url' => $this->confirm_success_url,
        ];

        $url = \Yii::$app->getUrlManager()->createAbsoluteUrl($query);

        $mailer = Yii::$app->getMailer();
        $mailer->setViewPath("{$this->module->viewPath}/mail");

        $mailer->compose('auth/confirm_email', [
                'email' => $user->email,
                'confirm_url' => $url,
            ])
            ->setFrom(\Yii::$app->params['adminEmail'])
            ->setTo($user->email)
            ->setSubject('Email confirmation')
            ->send();
    }

}
