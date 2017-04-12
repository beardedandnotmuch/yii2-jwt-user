<?php

namespace beardedandnotmuch\user\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $login;

    public $password;

    /**
     * @var User
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
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
        $user = $this->getUser();

        if ($user === null || !Yii::$app->security->validatePassword($this->password, $user->password_hash)) {
            $this->addError($attribute, Yii::t('app', 'Invalid login or password'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['password']);

        return $fields;
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
        if ($this->user === null) {
            $this->user = User::find(['email' => $this->login])->one();
        }

        return $this->user;
    }
}
