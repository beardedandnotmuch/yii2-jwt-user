<?php

namespace beardedandnotmuch\user\events;

use yii\base\Event;

class AfterEmailConfirmationEvent extends Event
{
    /**
     * @var User
     */
    public $user;

}
