<?php

namespace beardedandnotmuch\user\controllers;

use Yii;
use beardedandnotmuch\user\helpers\Token;
use beardedandnotmuch\user\models\User;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use beardedandnotmuch\user\Module;
use beardedandnotmuch\user\events\AfterEmailConfirmationEvent;

class ConfirmController extends BaseController
{
    /**
     * Marks email of user as confirmed.
     *
     * @return void
     */
    public function actionIndex()
    {
        $token = $this->request->post('token');

        try {
            $data = Token::decode($token);
        } catch (Exception $e) {
            throw new BadRequestHttpException('Confirm token is invalid');
        }

        if (empty($data['id'])) {
            throw new BadRequestHttpException('Confirm token is invalid');
        }

        if (empty($data['token'])) {
            throw new BadRequestHttpException('Confirm token is invalid');
        }

        $user = User::findOne($data['id']);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        if (!$this->security->validatePassword($data['token'], $user->confirm_token_hash)) {
            throw new BadRequestHttpException('Confirm token is invalid');
        }

        $transaction = $this->db->beginTransaction();

        try {
            $user->confirm_token_hash = null;
            $result = $user->save(false, ['confirm_token_hash']);

            if (!$result) {
                throw new \Exception('Validation error');
            }

            $this->module->trigger(Module::EVENT_AFTER_EMAIL_CONFIRMATION, Yii::createObject([
                'class' => AfterEmailConfirmationEvent::class,
                'user' => $user,
            ]));

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();

            throw $e;
        }

        return ['success' => true];
    }

}
