<?php

namespace beardedandnotmuch\user;

use Yii;
use yii\base\Module as BaseModule;
use yii\web\User as WebUser;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;

class Module extends BaseModule implements BootstrapInterface
{
    /** @var array Model map */
    public $modelMap = [
        'User'             => 'beardedandnotmuch\user\models\User',
        'LoginForm'        => 'beardedandnotmuch\user\models\LoginForm',
        'PasswordForm'     => 'beardedandnotmuch\user\models\PasswordForm',
        'RegistrationForm' => 'beardedandnotmuch\user\models\RegistrationForm',
    ];

    /**
     * @var bool
     * Login user right after registration.
     */
    public $forceLogin = false;

    /**
     * @var string The prefix for user module URL.
     *
     * @See [[GroupUrlRule::prefix]]
     */
    public $urlPrefix = 'api/user';

    /** @var array The rules to be used in URL management. */
    public $urlRules = [
        'OPTIONS <_c:.+>' => 'default/options',

        'POST session' => 'session/create',
        'DELETE session' => 'session/delete',

        'POST auth' => 'registrations/create',
        'PUT auth' => 'registrations/update',
        'DELETE auth' => 'registrations/delete',

        'GET /' => 'user/get',
        'PUT /' => 'user/update',

        'PUT password' => 'password/update',
        'POST password/reset' => 'password/reset',
        'PUT password/reset' => 'password/replace',

        'POST heartbeat' => 'token/update',
    ];

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        foreach ($this->modelMap as $name => $definition) {
            $class = "beardedandnotmuch\\user\\models\\" . $name;
            Yii::$container->set($class, $definition);
            $modelName = is_array($definition) ? $definition['class'] : $definition;
            $this->modelMap[$name] = $modelName;
        }

        if ($app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'beardedandnotmuch\user\commands';
        } else {
            Yii::$container->set('yii\web\User', [
                'identityClass' => $this->modelMap['User'],
                'enableAutoLogin' => false,
                'enableSession' => false,
                'loginUrl' => null,
            ]);

            $configUrlRule = [
                'prefix' => $this->urlPrefix,
                'routePrefix' => $this->id,
                'rules'  => [],
            ];

            foreach ($this->urlRules as $pattern => $action) {
                $configUrlRule['rules'][] = $this->createRule($pattern, $action);
            }

            $configUrlRule['class'] = 'yii\web\GroupUrlRule';
            $rule = Yii::createObject($configUrlRule);

            // Add module URL rules.
            $app->urlManager->addRules([$rule], false);
        }
    }

    /**
     * Creates a URL rule using the given pattern and action.
     * @param string $pattern
     * @param string $action
     * @return \yii\web\UrlRuleInterface
     */
    protected function createRule($pattern, $action)
    {
        $verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS';
        if (preg_match("/^((?:($verbs),)*($verbs))(?:\\s+(.*))?$/", $pattern, $matches)) {
            $verbs = explode(',', $matches[1]);
            $pattern = isset($matches[4]) ? $matches[4] : '';
        } else {
            $verbs = [];
        }

        $config['verb'] = $verbs;
        $config['pattern'] = rtrim($pattern, '/');
        $config['route'] = $action;

        if (!empty($verbs) && !in_array('GET', $verbs)) {
            $config['mode'] = \yii\web\UrlRule::PARSING_ONLY;
        }

        return $config;
    }
}
