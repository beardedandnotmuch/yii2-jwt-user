<?php

namespace beardedandnotmuch\user;

use Yii;
use yii\base\Module as BaseModule;
use yii\web\User as WebUser;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;

class Module extends BaseModule implements BootstrapInterface
{
    const EVENT_BEFORE_LOGIN = 'beforeLogin';
    const EVENT_AFTER_LOGIN = 'afterLogin';
    const EVENT_BEFORE_LOGOUT = 'beforeLogout';
    const EVENT_AFTER_LOGOUT = 'afterLogout';
    const EVENT_BEFORE_REGISTER = 'beforeRegistration';
    const EVENT_AFTER_REGISTER = 'afterRegistration';
    const EVENT_AFTER_EMAIL_CONFIRMATION = 'afterEmailConfirmation';
    const EVENT_SEND_RESET_PASSWORD = 'sendResetPassword';

    /** @var array Model map */
    public $modelMap = [
        'User'             => 'beardedandnotmuch\user\models\User',
        'LoginForm'        => 'beardedandnotmuch\user\models\LoginForm',
        'PasswordForm'     => 'beardedandnotmuch\user\models\PasswordForm',
        'RegistrationForm' => 'beardedandnotmuch\user\models\RegistrationForm',
    ];

    /**
     * @var bool|callable
     * Login user right after registration.
     */
    public $forceLogin = false;

    /**
     * @var bool|callable
     * Send token through cookie.
     */
    public $useCookie = false;

    /**
     * @var integer|bool|callable
     * Token become expired after this time.
     */
    public $duration = 24 * 60 * 60;

    /**
     * @var string The prefix for user module URL.
     *
     * @See [[GroupUrlRule::prefix]]
     */
    public $urlPrefix = 'api/user';

    /**
     * @var string
     * Path to the views.
     */
    public $viewPath = '@beardedandnotmuch/user/views';

    /** @var array The rules to be used in URL management. */
    public $urlRules = [
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

        'POST confirm' => 'confirm/index',

        'POST heartbeat' => 'token/update',
    ];

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        foreach ($this->modelMap as $name => $definition) {
            $class = "beardedandnotmuch\\user\\models\\$name";
            if (is_callable($definition)) {
                $definition = call_user_func_array($definition, [$app]);
            }

            Yii::$container->set($class, $definition);
            $modelName = is_array($definition) ? $definition['class'] : $definition;
            $this->modelMap[$name] = $modelName;
        }

        if (is_callable($this->forceLogin)) {
            $this->forceLogin = call_user_func_array($this->forceLogin, [$app]);
        }

        if (is_callable($this->useCookie)) {
            $this->useCookie = call_user_func_array($this->useCookie, [$app]);
        }

        if (is_callable($this->duration)) {
            $this->duration = call_user_func_array($this->duration, [$app]);
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

            // default events handlers
            if (!$this->hasEventHandlers(self::EVENT_SEND_RESET_PASSWORD)) {
                $this->on(self::EVENT_SEND_RESET_PASSWORD, [$this, 'sendResetPasswordInstruction']);
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

    /**
     * Send email with reset password instruction to the user.
     *
     * @param events\SendResetPasswordEvent $event
     *
     * @return bool
     */
    protected function sendResetPasswordInstruction($event)
    {
        $form = $event->form;
        $mailer = $event->mailer;

        $params = array_merge($form->getEmailParams(), [
            'url' => $form->createUrl(),
            'email' => $form->getEmail(),
        ]);

        return $mailer->compose('auth/reset_password', $params)
            ->setFrom(Yii::$app->params['adminEmail'])
            ->setTo($form->email)
            ->setSubject('Reset password instructions')
            ->send();
    }

}
