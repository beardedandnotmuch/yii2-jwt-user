<?php

namespace beardedandnotmuch\user\events;

use yii\base\Event;

class BeforeLoginEvent extends Event
{
    /**
     * @var LoginForm
     */
    public $form;

}

