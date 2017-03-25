<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\auth\HttpBearerAuth;
use beardedandnotmuch\user\helpers\JWT;

class PasswordController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'authenticator' => [
                'class' => HttpBearerAuth::class,
            ],
        ]);
    }

    /**
     * Change user's password
     *
     * @throw BadRequestHttpException
     * @return array
     */
    public function actionChange()
    {
        $form = Yii::$container->get('beardedandnotmuch\user\models\PasswordForm');
        $form->setAttributes(Yii::$app->getRequest()->post());

        if (!$form->validate()) {
            return $form;
        }

        $user = Yii::$app->getUser()->getIdentity();

        $user->setPassword($form->new_password);

        if (!$user->save()) {
            throw new BadRequestHttpException();
        }

        return ['token' => JWT::token($user)];
    }
}
