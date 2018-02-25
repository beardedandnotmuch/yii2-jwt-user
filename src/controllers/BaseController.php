<?php

namespace beardedandnotmuch\user\controllers;

use yii\di\Instance;
use yii\rest\Controller;

abstract class BaseController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        $id,
        $module,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Returns instance of the db connection.
     *
     * @return yii\db\Connection
     */
    public function getDb($name = 'db')
    {
        return Instance::ensure($this->module->get($name), 'yii\db\Connection');
    }

    /**
     * Returns instance of User.
     *
     * @return yii\web\User
     */
    public function getUser()
    {
        return $this->module->get('user');
    }

    /**
     * Returns instance of the current request.
     *
     * @return yii\web\Request
     */
    public function getRequest()
    {
        return $this->module->get('request');
    }

    /**
     * Returns instance of the response.
     *
     * @return yii\web\Response
     */
    public function getResponse()
    {
        return $this->module->get('response');
    }

    /**
     * Returns instance of Security.
     *
     * @return yii\base\Security
     */
    public function getSecurity()
    {
        return $this->module->get('security');
    }

    /**
     * Returns mailer instance.
     *
     * @return yii\mail\MailerInterface
     */
    public function getMailer()
    {
        $mailer = $this->module->get('mailer');
        $mailer->setViewPath("{$this->module->viewPath}/mail");

        return $mailer;
    }

}
