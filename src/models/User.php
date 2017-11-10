<?php

namespace beardedandnotmuch\user\models;

use Yii;
use yii\db\ActiveRecord as BaseModel;
use yii\web\IdentityInterface;
use yii\filters\RateLimitInterface;
use yii\web\ServerErrorHttpException;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\web\UnauthorizedHttpException;
use Lcobucci\JWT\Parser as JWTParser;
use Lcobucci\JWT\Signer\Hmac\Sha256 as Signer;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property int $id
 * @property int $status
 * @property string $password_hash
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 */
class User extends BaseModel implements IdentityInterface, RateLimitInterface, JWTSourceInterface
{
    const EVENT_SEND_CONFIRMATION = 'sendConfirmation';
    const RATE_LIMIT_WINDOW = 600;

    const STATUS_CONFIRMED = 1;
    const STATUS_INVITED = 2;
    const STATUS_NEED_TO_FILL = 3;

    const SCENARIO_UPDATE = 'update';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [[
                'password_hash',
                'email',
                'confirm_token_hash',
                'reset_password_token_hash',
            ], 'string', 'max' => 255],
            ['email', 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();
        // remove fields that contain sensitive information
        unset(
            $fields['allowance'],
            $fields['created_at'],
            $fields['updated_at'],
            $fields['rate_limit'],
            $fields['password_hash'],
            $fields['confirm_token_hash'],
            $fields['allowance_updated_at'],
            $fields['reset_password_token_hash']
        );

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        return [];
    }

    /**
     * Search user by email.
     *
     * @param string $email Valid email.
     *
     * @return User|null
     */
    public static function findByEmail($value)
    {
        return static::findOne(['email' => $value]);
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
    }

    public function validateAuthKey($authKey)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRateLimit($request, $action)
    {
        return [$this->rate_limit, self::RATE_LIMIT_WINDOW];
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllowance($request, $action)
    {
        $value = null;
        if ($this->allowance_updated_at !== null) {
            $date = new \DateTime($this->allowance_updated_at, new \DateTimeZone('UTC'));
            $value = $date->format('U');
        }

        return [$this->allowance, $value];
    }

    /**
     * {@inheritdoc}
     */
    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $this->allowance = $allowance;
        $this->allowance_updated_at = gmdate('Y-m-d H:i:s', $timestamp);
        $this->save(true, ['allowance', 'allowance_updated_at']);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        try {

            if (DestroyedToken::isExist($token)) {
                throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
            }

            $token = (new JWTParser())->parse($token);
            $jti = $token->getHeader('jti');

            if (empty($jti)) {
                throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
            }

            $user = static::findOne($jti);

            if ($token->isExpired()) {
                throw new UnauthorizedHttpException('Your token is expired.');
            }

            if (!$token->verify(new Signer(), $user->getSecretKey())) {
                throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
            }

            return $user;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getSecretKey()
    {
        return $this->password_hash;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenClaims()
    {
        return [];
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getIsConfirmed()
    {
        return true;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getIsBlocked()
    {
        return false;
    }

    /**
     * undocumented function
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $security = Yii::$app->getSecurity();
        $this->setAttribute('password_hash', $security->generatePasswordHash($password));

        return $this;
    }

    /**
     * undocumented function
     *
     * @return $this
     */
    public function setConfirmToken($token)
    {
        $security = Yii::$app->getSecurity();
        $this->setAttribute('confirm_token_hash', $security->generatePasswordHash($token));

        return $this;
    }

    /**
     * undocumented function
     *
     * @return $this
     */
    public function setResetPasswordToken($token)
    {
        $security = Yii::$app->getSecurity();
        $this->setAttribute('reset_password_token_hash', $security->generatePasswordHash($token));

        return $this;
    }

    /**
     * @return \yii\db\ActiveRecord
     */
    public function getDestroyedTokens()
    {
        return $this->hasMany(DestroyedToken::class, ['user_id' => 'id']);
    }

}
