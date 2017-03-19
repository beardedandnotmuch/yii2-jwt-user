<?php

namespace beardedandnotmuch\user\filters;

use Yii;
use yii\base\ActionFilter as BaseFilter;

class UpdateAuthHeaders extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        $request = Yii::$app->getRequest();

        if ($request->getMethod() !== 'OPTIONS') {
            $get = function ($key) use ($request) {
                $value = $request->getHeaders()->get($key);
                if (empty($value)) {
                    $value = $request->get($key);
                }

                return $value;
            };

            $transaction = Yii::$app->getDb()->beginTransaction();
            $identity = Yii::$app->getUser()->getIdentity();
            $client = $get('client');
            try {
                if ($identity && !$identity->isBatchRequest($client)) {
                    // generate headers with new token...
                    Yii::$app->db->createCommand('SELECT 1 FROM {{%tokens}} WHERE `user_id` = :id AND `client_id` = :client_id FOR UPDATE', [':id' => $identity->id, ':client_id' => $client])->execute();
                    $authHeaders = $identity->createNewAuthHeaders($client);
                    $headers = Yii::$app->getResponse()->getHeaders();

                    // ...and set it to response
                    foreach ($authHeaders as $key => $value) {
                        $headers->set($key, $value);
                    }
                }
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollback();
                throw $e;
            }
        }

        return $result;
    }
}
