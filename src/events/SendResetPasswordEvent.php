<?php

namespace beardedandnotmuch\user\events;

use yii\base\Event;

class SendResetPasswordEvent extends Event
{
    /**
     * @var beardedandnotmuch\user\models\ResetPasswordForm
     */
    public $form;

    /**
     * @var yii\mail\MailerInterface
     */
    public $mailer;
}

