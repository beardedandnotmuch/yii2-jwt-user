<?php

namespace beardedandnotmuch\user\commands;

use Yii;
use yii\console\Controller;
use beardedandnotmuch\user\models\User;
use yii\helpers\Console;
use yii\validators\EmailValidator;
use yii\helpers\ArrayHelper;

/**
 * Users CRUD cli commands.
 * ./yii user/create 'admin' 'admin@rm.dev' 'admin' --role='admin'.
 */
class CreateController extends Controller
{
    const USERNAME_LENGTH = 6;
    const PASSWORD_LENGTH = 6;

    public $status = User::STATUS_CONFIRMED;
    public $amount = 1;
    public $password;

    /**
     * Create new user.
     */
    public function actionIndex($email = null, $password = null)
    {
        $security = Yii::$app->getSecurity();
        $validator = new EmailValidator();

        for ($i = 0; $i < $this->amount; ++$i) {
            if ($this->amount > 1) {
                $username = $security->generateRandomString(self::USERNAME_LENGTH);
                $email = $username . '@example.dev';
            }

            if (!$validator->validate($email)) {
                $this->stdout(Yii::t('app', 'Email is not valid') . "!\n", Console::FG_RED);
                exit(Controller::EXIT_CODE_ERROR);
            }

            if (empty($password)) {
                $password = $security->generateRandomString(self::PASSWORD_LENGTH);
            }

            $user = new User([
                'email' => $email,
                'status' => $this->status,
                'password_hash' => $security->generatePasswordHash($password),
            ]);

            if ($user->save()) {
                $this->stdout(Yii::t('app', "The user \"$email\" with password \"$password\" has been created") . "!\n", Console::FG_GREEN);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function options($action)
    {
        return ArrayHelper::merge(parent::options($action), ['status', 'amount']);
    }
}

