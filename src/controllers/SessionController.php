<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\web\User;
use yii\web\Cookie;
use yii\rest\Controller as BaseController;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\filters\auth\HttpBearerAuth;
use beardedandnotmuch\user\filters\UpdateToken;

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
                'class' => HttpBearerAuth::class,
                'only' => ['delete'],
            ],
            'updatetoken' => [
                'class' => UpdateToken::class,
                'only' => ['create'],
            ],
        ]);
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        if ($this->module->forceCookie) {
            Yii::$app->getUser()->on(User::EVENT_AFTER_LOGIN, function ($event) {
                Yii::$app->getResponse()->getCookies()->add(new Cookie([
                    'name' => $this->module->cookieName,
                    'value' => true,
                ]));
            });

            Yii::$app->getUser()->on(User::EVENT_AFTER_LOGOUT, function ($event) {
                Yii::$app->getResponse()->getCookies()->remove($this->module->cookieName);
            });
        }
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

        return $user->logout();
    }
}
