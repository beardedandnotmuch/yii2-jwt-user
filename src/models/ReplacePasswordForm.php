<?php

namespace beardedandnotmuch\user\models;

use Yii;
use yii\base\Model as BaseModel;
use yii\helpers\Json;
use Base64Url\Base64Url;
use yii\base\InvalidParamException;

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
        return [
            'requiredFields' => [['token', 'password', 'password_repeat'], 'required'],

            // password
            'passwordsAreEqual' => ['password', 'compare', 'compareAttribute' => 'password_repeat'],

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
            try {
                $this->payload = Json::decode(Base64Url::decode($this->token), false);
            } catch (InvalidParamException $e) {
                $this->payload = (object) ['id' => null, 'token' => null];
            }
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
            $payload = $this->getPayload();
            $class = Yii::$app->getUser()->identityClass;
            $this->user = $class::findOne($payload->id);
        }

        return $this->user;
    }
}

