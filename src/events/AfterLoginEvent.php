<?php

namespace beardedandnotmuch\user\events;

use yii\base\Event;

class AfterLoginEvent extends Event
{
    /**
     * @var LoginForm
     */
    public $form;

}
