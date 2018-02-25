<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use beardedandnotmuch\user\filters\AuthByToken;

class UserController extends BaseController
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
            ],
        ]);
    }

    /**
     * undocumented function
     *
     * @return User
     */
    public function actionGet()
    {
        $user = $this->user->getIdentity();
        $user->clearErrors();

        return $user;
    }

    /**
     * undocumented function
     *
     * @return User
     */
    public function actionUpdate()
    {
        $user = $this->user->getIdentity();
        $class = get_class($user);
        $user->setScenario($class::SCENARIO_UPDATE);
        $user->setAttributes($this->request->post());

        $user->save();

        return $user;
    }
}

