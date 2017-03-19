<?php

namespace beardedandnotmuch\user\controllers;

use yii\base\Controller as BaseController;

class DefaultController extends BaseController
{
    public $defaultAction = 'options';

    /**
     * Blank stub action for OPTIONS requests.
     *
     * @return bool
     */
    public function actionOptions()
    {
        // if it's OPTIONS request return valid headers
        // because yii\rest\OptionsAction is sucks :(
        return \Yii::$app->getRequest()->getMethod() === 'OPTIONS';
    }
}
