<?php

namespace beardedandnotmuch\user;

use Yii;
use yii\base\Module as BaseModule;
use yii\web\User as WebUser;

class Module extends BaseModule
{
    /** @var array Model map */
    public $modelMap = [];

    public $enableConfirmation = true;

    public $enableUnconfirmedLogin = false;

    public $enableGeneratingPassword = false;

    /**
     * @var string The prefix for user module URL.
     *
     * @See [[GroupUrlRule::prefix]]
     */
    public $urlPrefix = 'user';

    /** @var array The rules to be used in URL management. */
    public $urlRules = [
        'OPTIONS <_c:.+>' => 'default/options',

        'GET auth/validate_token' => 'token/validate',
        'GET auth/confirmation' => 'confirmations/index',

        'POST auth/password' => 'password/create',
        'PUT auth/password' => 'password/update',
        'GET auth/password' => 'password/index',

        'POST auth/sign_in' => 'session/create',
        'DELETE auth/sign_out' => 'session/delete',

        'POST auth' => 'registrations/create',
        'PUT auth' => 'registrations/update',
        'DELETE auth' => 'registrations/delete',
    ];

    /**
     * {@inheritdoc}
     */
    // public function init()
    // {
    //     parent::init();
    //     Yii::setAlias('@users', __DIR__);
    //     Yii::configure($this, require(__DIR__ . '/config.php'));
    // }
}
