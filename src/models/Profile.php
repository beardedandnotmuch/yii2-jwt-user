<?php

namespace beardedandnotmuch\user\models;

use Yii;
use app\models\BaseModel;
use yii\helpers\Url;
use app\models\AdminSettings;

class UserProfile extends BaseModel implements \app\rbac\OwnerInterface
{
    public $newRoleName;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return '{{%profiles}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function find()
    {
        return Yii::createObject(ProfileQuery::className(), [get_called_class()]);
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $user = Yii::$app->getUser();
        $userId = $user->getId();
        $roles = array_keys(Yii::$app->authManager->getRolesByUser($userId));

        // default set of fields + user's role
        $fields = array_merge(parent::fields(), [
            'role',
            'avatar_url' => function () {
                return $this->file ? Url::to("@web/uploads/{$this->file->filename}", true) : null;
            },
            'avatar_thumbnail_url' => function () {
                return $this->file ? Url::to("@web/uploads/thumbnails/{$this->file->filename}", true) : null;
            },
        ]);

        // if user has admin's role we must add in response all available roles.
        if (in_array('admin', $roles)) {
            $fields['roles'] = function () {
                return Yii::$app->authManager->getRoles();
            };

            $fields['hidden_email'] = function () {
                return current(explode('@', $this->user->email)) . '@***.'
                    . end(explode('.', $this->user->email));
            };

            $fields['status'] = function () {
                switch ($this->user->status) {
                    case User::STATUS_INVITED:
                        return 'invited';

                    case User::STATUS_CONFIRMED:
                        return 'confirmed';

                    case User::STATUS_NEED_TO_FILL:
                        return 'need_to_fill_out';
                }
            };
        }

        // we wanna always see these system's fields
        return array_merge($fields, [
            'is_permitted_to_edit' => function () use ($user) {
                return $user->can('updateProfile', ['model' => $this]);
            },
            'is_permitted_to_view' => function () {
                return true;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (!empty($this->newRoleName) && $this->role !== $this->newRoleName) {
            $user = Yii::$app->getUser();
            $roles = Yii::$app->authManager->getRoles();

            // change role of profile
            if ($user->can('changeRole') && array_key_exists($this->newRoleName, $roles)) {
                Yii::$app->authManager->revokeAll($this->id);
                Yii::$app->authManager->assign($roles[$this->newRoleName], $this->id);
            }
        }

        if ($insert) {
            $playlist = new Playlist();
            $playlist->setAttributes([
                'name' => 'Favorite',
                'description' => 'Playlist with favorite songs',
                'is_favorite' => 1,
            ]);

            $this->link('playlists', $playlist);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'nickname'], 'string', 'max' => 255],
            [[
                'file_id',
            ], 'safe'],
            ['role', 'in', 'range' => array_keys(Yii::$app->authManager->getRoles())],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne('beardedandnotmuch\user\models\User', ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne('app\models\File', ['id' => 'file_id']);
    }

    /**
     * Returns user's logs.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTrails()
    {
        return $this->hasMany('beardedandnotmuch\user\models\Trail', ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasMany('beardedandnotmuch\user\models\UserGroup', ['id' => 'group_id'])
            ->viaTable('{{%groups_has_users}}', ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlaylists()
    {
        return $this->hasMany('beardedandnotmuch\user\models\Playlist', ['user_id' => 'id']);
    }

    /**
     * Returns role of current user.
     *
     * Note: it always returns role from authManager, even if you set it by
     * UserProfile::setRole. This made for UserProfile::afterSave logic,
     * it can now determine if role was changed.
     *
     * @return string
     */
    public function getRole()
    {
        return key(Yii::$app->authManager->getRolesByUser($this->id));
    }

    /**
     * Setter for role's name that was taken from request.
     *
     * Note: this method doesn't have influence to the UserProfile::getRole
     * results.
     */
    public function setRole($value)
    {
        $this->newRoleName = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        return [
            'file',
            'user',
            'trails',
            'playlists',
            'groups',
            'configs' => function () {
                return AdminSettings::populate(Yii::$app->settings);
            },
            'role'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isOwner($user)
    {
        return $this->id == $user;
    }
}
