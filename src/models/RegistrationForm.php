<?php

namespace beardedandnotmuch\user\models;

use Yii;
use yii\base\Model;

class RegistrationForm extends Model
{
    /**
     * @var string User email address
     */
    public $email;

    /**
     * @var string Password
     */
    public $password;

    public $confirm_success_url;

    protected $confirm_token;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $user = get_class(Yii::$container->get('beardedandnotmuch\user\models\User'));

        return [
            // email rules
            'emailTrim'     => ['email', 'filter', 'filter' => 'trim'],
            'emailRequired' => ['email', 'required'],
            'emailPattern'  => ['email', 'email'],
            'emailUnique'   => [
                'email',
                'unique',
                'targetClass' => $user,
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
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }

    /**
     * Registers a new user account. If registration was successful it will set flash message.
     *
     * @return bool
     */
    public function register()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var User $user */
        $user = Yii::createObject(User::class);
        $this->loadAttributes($user);

        $this->sendConfirmation($user);

        if (!$user->save()) {
            throw new \Exception('not saved');
        }

        return $user;
    }

    /**
     * Loads attributes to the user model. You should override this method if you are going to add new fields to the
     * registration form. You can read more in special guide.
     *
     * By default this method set all attributes of this model to the attributes of User model, so you should properly
     * configure safe attributes of your User model.
     *
     * @param User $user
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
        $mailer->setViewPath('@beardedandnotmuch/user/views/mail');

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
