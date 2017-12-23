<?php

namespace beardedandnotmuch\user\events;

use yii\base\Event;

class AfterLogoutEvent extends Event
{
    /**
     * @var yii\web\IdentityInterface
     */
    public $identity;

}

