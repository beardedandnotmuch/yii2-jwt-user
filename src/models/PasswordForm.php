<?php

namespace beardedandnotmuch\user\models;

use Yii;
use beardedandnotmuch\user\traits\ModuleTrait;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;

class PasswordForm extends Model
{
    use ModuleTrait;

    public $old_password;

    public $new_password;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            'requiredFields' => [['old_password', 'new_password'], 'required'],
            'passwordLength'   => ['new_password', 'string', 'min' => 6, 'max' => 72],
            'passwordValidate' => ['old_password', 'validatePassword'],
        ];
    }

    /**
     * @return void
     */
    public function validatePassword($attribute, $params, $validator)
    {
        $security = Yii::$app->getSecurity();
        $user = Yii::$app->getUser()->getIdentity();

        if (!$user) {
            $this->addError($attribute, 'Old password is incorrect');

            return;
        }

        if (!$security->validatePassword($this->$attribute, $user->password_hash)) {
            $this->addError($attribute, 'Old password is incorrect');

            return;
        }
    }
}
