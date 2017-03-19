<?php

namespace beardedandnotmuch\user\traits;

use beardedandnotmuch\user\Module;

/**
 * Trait ModuleTrait
 * @property-read Module $module
 * @package beardedandnotmuch\user\traits
 */
trait ModuleTrait
{
    /**
     * @return Module
     */
    public function getModule()
    {
        return \Yii::$app->getModule('user');
    }
}
