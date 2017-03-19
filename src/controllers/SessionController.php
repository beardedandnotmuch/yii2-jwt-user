<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use yii\rest\Controller as BaseController;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use beardedandnotmuch\user\traits\ModuleTrait;
use yii\filters\auth\HttpBearerAuth;
use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\Signer\Hmac\Sha256 as Signer;

class SessionController extends BaseController
{
    use ModuleTrait;

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
        $model = Yii::createObject($this->module->modelMap['LoginForm']);
        $request = Yii::$app->getRequest();

        if ($model->load($request->post()) && $model->login()) {
            $user = $model->getUser();

            $now = time();

            $token = (new JWTBuilder())
                ->setIssuer($request->hostInfo)
                ->setAudience($request->hostInfo)
                ->setId($user->getId(), true)
                ->setIssuedAt($now)
                ->setNotBefore($now)
                ->setExpiration($now + 3600)
                ->sign(new Signer(), $user->getSecretKey())
                ->getToken();

            return ['token' => (string) $token];
        }

        return $model;
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
