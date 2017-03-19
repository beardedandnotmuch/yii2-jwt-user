<?php

namespace beardedandnotmuch\user\models;

use Yii;
use beardedandnotmuch\user\traits\ModuleTrait;
use yii\base\Model;

class LoginForm extends Model
{
    use ModuleTrait;

    public $login;

    public $password;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            'requiredFields' => [['login'], 'required'],
            'loginTrim' => ['login', 'trim'],
            'confirmationValidate' => [
                'login',
                function ($attribute) {
                    if ($this->user !== null) {
                        $confirmationRequired = $this->module->enableConfirmation && !$this->module->enableUnconfirmedLogin;

                        if ($confirmationRequired && !$this->user->getIsConfirmed()) {
                            $this->addError($attribute, Yii::t('user', 'You need to confirm your email address'));
                        }

                        if ($this->user->getIsBlocked()) {
                            $this->addError($attribute, Yii::t('user', 'Your account has been blocked'));
                        }
                    }
                }
            ],
            'requiredFields' => [['login', 'password'], 'required'],
            'passwordValidate' => ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates if the hash of the given password is identical to the saved hash in the database.
     * It will always succeed if the module is in DEBUG mode.
     *
     * @return void
     */
    public function validatePassword($attribute, $params, $validator)
    {
        if ($this->user === null || !Yii::$app->security->validatePassword($this->password, $this->user->password_hash)) {
            $this->addError($attribute, Yii::t('app', 'Invalid login or password'));
        }
    }

    /**
     * Validates form and logs the user in.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->getUser()->login($this->user);
        }

        return false;
    }

    /**
     * undocumented function
     *
     * @return User
     */
    public function getUser()
    {
        static $user = null;

        if ($user === null) {
            $user = User::find(['email' => trim($this->login)])->one();
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function formName()
    {
        return '';
    }
}
