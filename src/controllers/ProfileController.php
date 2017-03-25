<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\filters\auth\HttpBearerAuth;

class ProfileController extends BaseController
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
        return Yii::$app->getUser()->getIdentity();
    }

    /**
     * undocumented function
     *
     * @return User
     */
    public function actionUpdate()
    {
        $user = Yii::$app->getUser()->getIdentity();
        $user->setAttributes(Yii::$app->getRequest()->post());

        $user->save();

        return $user;
    }
}

