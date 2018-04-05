<?php

namespace beardedandnotmuch\user\models;

use Yii;
use Lcobucci\JWT\Parser as JWTParser;

/**
 * This is the model class for table "{{%user_destroyed_tokens}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $token_hash
 * @property string $expired_at
 * @property string $created_at
 *
 * @property User $user
 */
class DestroyedToken extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_destroyed_tokens}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['token_hash'], 'string', 'max' => 255],
            [['expired_at', 'created_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['token_hash'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'token_hash' => 'Token Hash',
            'expired_at' => 'Expired At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Factory method.
     *
     * @return DestroyedToken
     */
    public static function fromString($token)
    {
        $jwt = (new JWTParser())->parse((string) $token);

        $expiredAt = null;
        if ($jwt->hasClaim('exp')) {
            $expiredAt = date('Y-m-d H:i:s', $jwt->getClaim('exp'));
        }

        $model = new self();

        $model->setAttributes([
            'token_hash' => md5($token),
            'expired_at' => $expiredAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $model;
    }

    /**
     * Returns true if token was destroyed by user.
     *
     * @return boolean
     */
    public static function isExist($token)
    {
        return self::find()->andWhere(['token_hash' => md5($token)])->exists();
    }

}
