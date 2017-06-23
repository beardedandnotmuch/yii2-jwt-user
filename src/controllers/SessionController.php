<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use beardedandnotmuch\user\filters\UpdateToken;
use beardedandnotmuch\user\filters\AuthByToken;
use beardedandnotmuch\user\Module;

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
        $this->module->trigger(Module::EVENT_BEFORE_LOGIN);
        $request = Yii::$app->getRequest();
        $form = Yii::$container->get('beardedandnotmuch\user\models\LoginForm');
        $form->setAttributes($request->post());

        if (!$form->login()) {
            return $form;
        }

        $this->module->trigger(Module::EVENT_AFTER_LOGIN);

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
        $this->module->trigger(Module::EVENT_BEFORE_LOGOUNT);

        $user = Yii::$app->getUser();
        /*
         * @var yii\web\IdentityInterface
         */
        $identity = $user->getIdentity();

        if (!$identity) {
            throw new NotFoundHttpException('User was not found or was not logged in');
        }

        $response = Yii::$app->getResponse();
        $behavior = $this->getBehavior('updatetoken');

        if ($this->module->useCookie) {
            $response->getCookies()->remove($behavior->cookieName);
        } else {
            $response->getHeaders()->set($behavior->headerName, '');
        }

        $this->module->trigger(Module::EVENT_AFTER_LOGOUT);

        return $user->logout();
    }
}
