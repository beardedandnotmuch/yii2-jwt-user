<?php

namespace beardedandnotmuch\user\models;

use Yii;
use yii\base\Model as BaseModel;
use Base64Url\Base64Url;

class ReplacePasswordForm extends BaseModel
{
    public $token;

    public $password;

    public $password_repeat;

    protected $user;

    protected $payload;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $class = Yii::$app->getUser()->identityClass;

        return [
            'requiredFields' => [['token', 'password', 'password_repeat'], 'required'],

            // password
            ['password', 'compare', 'compareAttribute' => 'password_repeat'],

            // token
            'tokenIsValid' => ['token', 'validateToken'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function validateToken($attribute, $params, $validator)
    {
        $user = $this->getUser();
        $payload = $this->getPayload();

        if ($user === null || !Yii::$app->security->validatePassword($payload->token, $user->reset_password_token_hash)) {
            $this->addError($attribute, Yii::t('app', 'Invalid token'));
        }
    }

    /**
     * undocumented function
     *
     * @return stdObject
     */
    public function getPayload()
    {
        if (!$this->payload) {
            $this->payload = json_decode(Base64Url::decode($this->token));
        }

        return $this->payload;
    }

    /**
     * undocumented function
     *
     * @return User
     */
    public function getUser()
    {
        if ($this->user === null) {
            $class = Yii::$app->getUser()->identityClass;
            $this->user = $class::findOne($this->getPayload()->id);
        }

        return $this->user;
    }
}

