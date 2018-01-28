# yii2-jwt-user

Yii2 module which allows to authenticate users by using JWT tokens

Under development.

No tests yet.

# Example of config

```php
...
'modules' => [
    'auth' => [
        'class' => 'beardedandnotmuch\user\Module',
        'viewPath' => '@frontend/views',
        'forceLogin' => true,
        'duration' => function () {
          return is_mobile() ? false : 24 * 60 * 60;
        },
        'modelMap' => [
            'User' => 'frontend\models\User',
            'LoginForm' => 'frontend\models\LoginForm',
            'ReplacePasswordForm' => 'frontend\models\ReplacePasswordForm',
            'ResetPasswordForm' => 'frontend\models\ResetPasswordForm',
            'RegistrationForm' => [
                'class' => 'frontend\models\RegistrationForm',
                'key' => $params['registration.salt'],
            ],
        ],
        'on beforeLogin' => ['frontend\helpers\EventsHelper', 'beforeLogin'],
        'on afterLogin' => ['frontend\helpers\EventsHelper', 'afterLogin'],
        'on afterLogout' => ['frontend\helpers\EventsHelper', 'afterLogout'],
        'on afterRegistration' => ['frontend\helpers\EventsHelper', 'afterRegistration'],
        'on sendResetPassword' => ['frontend\helpers\EventsHelper', 'sendResetPassword'],
    ],
],
...
```

# Default routes

```
GET|PUT /api/user

POST|DELETE /api/user/session

POST /api/user/auth

PUT /api/user/password
POST|PUT /api/user/password/reset

POST confirm
POST heartbeat
```
