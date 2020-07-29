<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use beardedandnotmuch\user\filters\UpdateToken;
use beardedandnotmuch\user\filters\AuthByToken;
use beardedandnotmuch\user\Module;
use beardedandnotmuch\user\models\DestroyedToken;
use beardedandnotmuch\user\events\BeforeLoginEvent;
use beardedandnotmuch\user\events\AfterLoginEvent;
use beardedandnotmuch\user\events\AfterLogoutEvent;
use yii\web\Application;

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
                'sourceOrder' => $this->module->tokenSourceOrder,
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
        $form = Yii::$container->get('beardedandnotmuch\user\models\LoginForm');
        $form->setAttributes($this->request->post());

        $this->module->trigger(Module::EVENT_BEFORE_LOGIN, Yii::createObject([
            'class' => BeforeLoginEvent::class,
            'form' => $form,
        ]));

        if (!$form->login()) {
            return $form;
        }

        Yii::$app->on(Application::EVENT_AFTER_REQUEST, function ($event) use ($form) {
            $this->module->trigger(Module::EVENT_AFTER_LOGIN, Yii::createObject([
                'class' => AfterLoginEvent::class,
                'form' => $form,
            ]));
        });

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
        $this->module->trigger(Module::EVENT_BEFORE_LOGOUT);

        $user = $this->user;
        $request = $this->request;
        $response = $this->response;

        /*
         * @var yii\web\IdentityInterface
         */
        $identity = $user->getIdentity();

        if (!$identity) {
            throw new NotFoundHttpException('User was not found or was not logged in');
        }

        $behavior = $this->getBehavior('updatetoken');

        if ($this->module->useCookie) {
            $response->getCookies()->remove($behavior->cookieName);
        } else {
            $response->getHeaders()->set($behavior->headerName, '');
        }

        $identity->setAuthToken('');

        $token = $this->getBehavior('authenticator')->getToken($request);
        $identity->link('destroyedTokens', DestroyedToken::fromString($token));

        $this->module->trigger(Module::EVENT_AFTER_LOGOUT, Yii::createObject([
            'class' => AfterLogoutEvent::class,
            'identity' => $identity,
        ]));

        return $user->logout();
    }
}
