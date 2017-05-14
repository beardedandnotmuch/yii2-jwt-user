<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use beardedandnotmuch\user\filters\UpdateToken;
use beardedandnotmuch\user\filters\AuthByToken;

class SessionController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        // we don't needs any predefined behaviors of this controller.
        return array_merge(parent::behaviors(), [
            'authenticator' => [
                'class' => AuthByToken::class,
                'only' => ['delete'],
            ],
            'updatetoken' => [
                'class' => UpdateToken::class,
                'useCookie' => $this->module->useCookie,
                'duration' => $this->module->duration,
                'only' => ['create'],
            ],
        ]);
    }

    /**
     * Create new user's session.
     *
     * @return array
     * @throw UnauthorizedHttpException
     */
    public function actionCreate()
    {
        $request = Yii::$app->getRequest();
        $form = Yii::$container->get('beardedandnotmuch\user\models\LoginForm');
        $form->setAttributes($request->post());

        if (!$form->login()) {
            return $form;
        }

        return $form->toArray();
    }

    /**
     * Destroy user's session.
     *
     * @return bool
     * @throw yii\web\NotFoundHttpException
     */
    public function actionDelete()
    {
        $user = Yii::$app->getUser();
        /*
         * @var yii\web\IdentityInterface
         */
        $identity = $user->getIdentity();

        if (!$identity) {
            throw new NotFoundHttpException('User was not found or was not logged in');
        }

        if ($this->module->useCookie) {
            Yii::$app->getResponse()->getCookies()->remove('token');
        }

        return $user->logout();
    }
}
