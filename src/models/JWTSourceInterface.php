<?php

namespace beardedandnotmuch\user\models;

interface JWTSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey($asArray = false);

    /**
     * undocumented function
     *
     * @return string
     */
    public function getSecretKey();

}
