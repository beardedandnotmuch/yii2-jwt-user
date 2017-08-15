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

    /**
     * Returns claims that should be added to jwt token.
     *
     * @return array
     */
    public function getTokenClaims();

}
