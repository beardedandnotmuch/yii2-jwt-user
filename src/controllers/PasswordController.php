<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use app\models\Mail;
use beardedandnotmuch\user\models\User;
use yii\rest\Controller as BaseController;
use yii\web\BadRequestHttpException;

class PasswordController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        // we don't needs any predefined behaviors of this controller.
        return array_merge(parent::behaviors(), [
            'authenticator' => [
                'class' => 'beardedandnotmuch\user\filters\NgTokenAuth',
                'only' => ['update'],
            ],
            'headersUpdater' => [
                'class' => 'beardedandnotmuch\user\filters\UpdateAuthHeaders',
                'only' => ['update'],
            ],
        ]);
    }

    /**
     * Action will be invoked on password reset request.
     *
     * @return array
     * @throw \yii\web\BadRequestHttpException
     */
    public function actionCreate()
    {
        $security = Yii::$app->getSecurity();
        $params = Yii::$app->getRequest()->post();
        $validator = new \yii\validators\EmailValidator();

        if (empty($params['redirect_url'])) {
            throw new \yii\web\BadRequestHttpException('Missing `redirect_url` param.');
        }

        if (empty($params['email']) || !$validator->validate($params['email'])) {
            throw new \yii\web\BadRequestHttpException('Not valid request');
        }

        $user = User::findByEmail($params['email']);

        if (!$user) {
            throw new \yii\web\BadRequestHttpException('The mail has been sent');
        }

        $params['token'] = $security->generateRandomString();
        $user->reset_password_token_hash = $security->generatePasswordHash($params['token']);

        if (!$user->save()) {
            throw new \Exception('User wasnt saved');
        }

        $this->sendResetPasswordInstruction($user, $params);
        Yii::info("Reset password instructions was sent to {$params['email']}", "rm.users.reset.{$user->id}");
    }

    /**
     * Send email with reset password instruction to the user.
     *
     * @param User  $user
     * @param array $params
     *
     * @return bool
     */
    protected function sendResetPasswordInstruction(User $user, array $params)
    {
        $query = [
            'auth/password',
            'uid' => $user->id,
            'token' => $params['token'],
            // 'config' => $params['config_name'],
            'redirect_url' => $params['redirect_url'],
        ];

        $url = \Yii::$app->getUrlManager()->createAbsoluteUrl($query);

        $mailParams = [
            'template'   => 'auth/reset_password',
            'params' => [
                'email' => $user->email,
                'url' => $url,
                'text' => 'Someone has requested a link to change your password. You can do this through the link below',
            ],
            'status' => Mail::STATUS_PASSWORD_RESET,
            'title' => 'Reset password instructions',
        ];

        return !Mail::sendMail($user, $mailParams)->hasErrors();
    }

    /**
     * Thin action is where users arrive after visiting the email confirmation
     * link.
     *
     * @return array
     * @throw new \yii\web\NotFoundHttpException
     */
    public function actionIndex($uid, $token, $redirect_url)
    {
        $security = Yii::$app->getSecurity();
        $user = User::findOne($uid);

        if ($user && $security->validatePassword($token, $user->reset_password_token_hash)) {
            // NOTE: User::save will be invoked in User::createNewAuthHeaders
            $headers = $user->createNewAuthHeaders();

            $query = [
                'client_id' => $headers['client'],
                'token' => $headers['access-token'],
                'expiry' => $headers['expiry'],
                'uid' => $headers['uid'],
                'config' => 'default',
                'reset_password' => 'true',
            ];

            $url = \beardedandnotmuch\user\helpers\Url::generateUrl($redirect_url, $query);

            $this->redirect($url);
        }

        throw new \yii\web\NotFoundHttpException('Not found');
    }

    /**
     * Save new user's password.
     *
     * @return array
     * @throw \yii\web\BadRequestHttpException
     * @throw \Exception
     */
    public function actionUpdate()
    {
        $security = Yii::$app->getSecurity();
        $params = Yii::$app->getRequest()->post();

        // TODO: use validation
        if (empty($params['password']) || empty($params['password_confirmation'])) {
            throw new BadRequestHttpException('Not valid request');
        }

        if ($params['password'] !== $params['password_confirmation']) {
            throw new BadRequestHttpException('Passwords not match');
        }

        $user = Yii::$app->getUser()->getIdentity();

        $isValid = array_key_exists('old_password', $params)
            ? $security->validatePassword($params['old_password'], $user->password_hash)
            : false;

        if (empty($user->reset_password_token_hash) && !$isValid) {
            throw new BadRequestHttpException('Invalid token');
        }

        $user->password_hash = $security->generatePasswordHash($params['password']);
        // hash should be valid only once
        $user->reset_password_token_hash = '';

        if (!$user->save()) {
            throw new \Exception('Something going wrong');
        }

        return $user;
    }
}
