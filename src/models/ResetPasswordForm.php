<?php

namespace beardedandnotmuch\user\models;

use Yii;
use yii\base\Model as BaseModel;
use Base64Url\Base64Url;
use League\Uri\Schemes\Http;
use League\Uri\Modifiers\MergeQuery;

class ResetPasswordForm extends BaseModel
{
    public $email;

    public $redirect_url;

    protected $user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $class = Yii::$app->getUser()->identityClass;

        return [
            'requiredFields' => [['email', 'redirect_url'], 'required'],
            // email rules
            'emailTrim'     => ['email', 'filter', 'filter' => 'trim'],
            'emailPattern'  => ['email', 'email'],
            'emailExists'   => [
                'email',
                'exist',
                'targetClass' => $class,
                'message' => Yii::t('app', 'Email doesnt exists'),
                'targetAttribute' => 'email',
            ],

            // redirect_url
            'redirectUrl' => ['redirect_url', 'url'],
        ];
    }

    /**
     * Create token for reset password.
     *
     * @return string
     */
    public function createToken()
    {
        $token = password(16);
        $user = $this->getUser();
        $user->setResetPasswordToken($token)->save(false);

        return Base64Url::encode(json_encode([
            'id' => $user->id,
            'token' => $token,
        ]));
    }

    /**
     * Returns url for email.
     *
     * @return string
     */
    public function createUrl()
    {
        $uri = Http::createFromString($this->redirect_url);
        $modifier = new MergeQuery("token={$this->createToken()}");

        return (string) $modifier->process($uri);
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
            $this->user = $class::find()->where(['email' => $this->email])->one();
        }

        return $this->user;
    }
}
