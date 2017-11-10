<?php

namespace beardedandnotmuch\user\events;

use yii\base\Event;

class AfterRegistrationEvent extends Event
{
    /**
     * @var RegistrationForm
     */
    public $form;

}
