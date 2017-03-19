<?php

namespace beardedandnotmuch\user\models;

use Yii;
use beardedandnotmuch\user\traits\ModuleTrait;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%tokens}}".
 *
 * @property int $id
 * @property string $hash
 * @property int $user_id
 * @property string $client_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $expires_at
 * @property Users $user
 */
class Token extends \yii\db\ActiveRecord
{
    use ModuleTrait;

    const BATCH_REQUEST_BUFFER_THROTTLE = 10;

    public $is_expired = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tokens}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['created_at', 'updated_at', 'expires_at'], 'safe'],
            [['token_hash', 'last_token_hash', 'client_id'], 'string', 'max' => 255],
            [['user_id', 'client_id'], 'unique', 'targetAttribute' => ['user_id', 'client_id'], 'message' => 'The combination of User ID and Client ID has already been taken.'],
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
    public static function find()
    {
        $columns = array_keys(static::getTableSchema()->columns);
        $columns = array_merge($columns,  ['expires_at < NOW() as is_expired']);

        return parent::find()->select($columns);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne($this->module->modelMap['User'], ['id' => 'user_id']);
    }

    /**
     * Check is token is valid.
     *
     * @return bool
     */
    public function isCurrent($token)
    {
        $security = Yii::$app->getSecurity();
        $isValid = $security->validatePassword($token, $this->token_hash);

        return $isValid && !$this->is_expired;
    }

    /**
     * undocumented function.
     */
    public function isReusable($token)
    {
        $security = Yii::$app->getSecurity();
        $isValid = $this->last_token_hash && $security->validatePassword($token, $this->last_token_hash);

        return $isValid && strtotime($this->updated_at) > time() - self::BATCH_REQUEST_BUFFER_THROTTLE;
    }
}
