<?php

namespace beardedandnotmuch\user\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
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
            'requiredFields' => [['login', 'password'], 'required'],
            'passwordValidate' => ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates if the hash of the given password is identical to the saved hash in the database.
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
