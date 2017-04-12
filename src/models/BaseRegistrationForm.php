<?php

namespace beardedandnotmuch\user\models;

use Yii;
use yii\base\Model;

abstract class BaseRegistrationForm extends Model
{
    /**
     * @var string User email address
     */
    public $email;

    /**
     * @var string Password
     */
    public $password;

    /**
     * @var User
     */
    protected $user;

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
     * undocumented function
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Registers a new user account.
     *
     * @return User
     */
    public function register()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var User $user */
        $user = Yii::$container->get('beardedandnotmuch\user\models\User');
        $this->loadAttributes($user);

        $this->setUser($user);

        $result = $user->save();

        $this->addErrors($user->getErrors());

        return $result;
    }

    /**
     * Loads attributes to the user model.
     *
     * @param User $user
     */
    protected abstract function loadAttributes($user);
}
