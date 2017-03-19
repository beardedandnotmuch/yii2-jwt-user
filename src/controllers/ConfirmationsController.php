<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use beardedandnotmuch\user\models\User;

class ConfirmationsController extends BaseController
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
                'only' => ['update', 'destroy'],
            ],
            'headersUpdater' => [
                'class' => 'beardedandnotmuch\user\filters\UpdateAuthHeaders',
                'only' => ['create', 'destroy'],
            ],
        ]);
    }

    /**
     * Update user's confirmation info.
     *
     * @throw \yii\web\NotFoundHttpException
     */
    public function actionIndex($uid, $token, $config, $redirect_url)
    {
        $security = Yii::$app->getSecurity();
        $authManager = Yii::$app->getAuthManager();
        $user = User::findOne($uid);

        if (!$user) {
            throw new \yii\web\NotFoundHttpException('User not found');
        }

        if ($user->status !== User::STATUS_INVITED) {
            throw new \yii\web\BadRequestHttpException('User already apply invation');
        }

        if (!$security->validatePassword($token, $user->confirm_token_hash)) {
            throw new \yii\web\BadRequestHttpException('Token invalid');
        }

        $user->confirm_token_hash = null;
        $headers = $user->createNewAuthHeaders($config);

        if (!$user->save()) {
            throw new \Exception('Something going wrong');
        }

        $query = [
            'token' => $headers['access-token'],
            'client_id' => $headers['client'],
            'expiry' => $headers['expiry'],
            'uid' => $headers['uid'],
            'account_confirmation_success' => true,
            'config' => $config,
        ];

        $url = \beardedandnotmuch\user\helpers\Url::generateUrl($redirect_url, $query);

        $roles = array_keys($authManager->getRolesByUser($uid));

        if (in_array('uRU', $roles)) {
            $authManager->revoke($authManager->getRole('uRU'), $uid);
        }

        if (!in_array('RU', $roles)) {
            $authManager->assign($authManager->getRole('RU'), $uid);
        }

        $this->redirect($url);
    }
}
