<?php

namespace beardedandnotmuch\user\models;

use Yii;
use yii\db\ActiveRecord as BaseModel;
use yii\web\IdentityInterface;
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
abstract class BaseUser extends BaseModel implements IdentityInterface, JWTSourceInterface
{
    const EVENT_SEND_CONFIRMATION = 'sendConfirmation';

    const STATUS_CONFIRMED = 1;
    const STATUS_INVITED = 2;
    const STATUS_NEED_TO_FILL = 3;

    const SCENARIO_UPDATE = 'update';

    /**
     * @var string
     */
    protected $authToken;

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
            $fields['created_at'],
            $fields['updated_at'],
            $fields['password_hash'],
            $fields['confirm_token_hash'],
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

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthToken(string $token)
    {
        $this->authToken = $token;
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

            if ($token->isExpired()) {
                throw new UnauthorizedHttpException('Your token is expired.');
            }

            $jti = $token->getHeader('jti');

            if (empty($jti)) {
                throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
            }

            $user = static::findOne($jti);

            if (!$user) {
                throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
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
     * {@inheritdoc}
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
     * @return boolean
     */
    public function getIsConfirmed()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function getIsBlocked()
    {
        return false;
    }

    /**
     * @return $this
     */
    public function setPassword($password)
    {
        $security = Yii::$app->getSecurity();
        $this->setAttribute('password_hash', $security->generatePasswordHash($password));

        return $this;
    }

    /**
     * @return $this
     */
    public function setConfirmToken($token)
    {
        $security = Yii::$app->getSecurity();
        $this->setAttribute('confirm_token_hash', $security->generatePasswordHash($token));

        return $this;
    }

    /**
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
