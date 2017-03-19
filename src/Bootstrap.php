<?php

namespace beardedandnotmuch\user;

use Yii;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;

class Bootstrap implements BootstrapInterface
{
    /** @var array Model's map */
    private $modelMap = [
        'User'             => 'beardedandnotmuch\user\models\User',
        'Token'            => 'beardedandnotmuch\user\models\Token',
        'LoginForm'        => 'beardedandnotmuch\user\models\LoginForm',
    ];

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        if ($app->hasModule('user') && ($module = $app->getModule('user')) instanceof Module) {
            $this->modelMap = array_merge($this->modelMap, $module->modelMap);
            foreach ($this->modelMap as $name => $definition) {
                $class = "beardedandnotmuch\\user\\models\\" . $name;
                Yii::$container->set($class, $definition);
                $modelName = is_array($definition) ? $definition['class'] : $definition;
                $module->modelMap[$name] = $modelName;
            }

            if ($app instanceof ConsoleApplication) {
                $module->controllerNamespace = 'beardedandnotmuch\user\commands';
            } else {
                Yii::$container->set('yii\web\User', [
                    'identityClass' => $module->modelMap['User'],
                    'enableAutoLogin' => false,
                    'enableSession' => false,
                    'loginUrl' => null,
                ]);

                $configUrlRule = [
                    'prefix' => $module->urlPrefix,
                    'rules'  => [],
                ];

                foreach ($module->urlRules as $pattern => $action) {
                    $configUrlRule['rules'][] = $this->createRule($pattern, $action);
                }

                if ($module->urlPrefix != 'user') {
                    $configUrlRule['routePrefix'] = 'user';
                }

                $configUrlRule['class'] = 'yii\web\GroupUrlRule';
                $rule = Yii::createObject($configUrlRule);

                // Add module URL rules.
                $app->urlManager->addRules([$rule], false);
            }
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
