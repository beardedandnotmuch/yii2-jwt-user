<?php

namespace beardedandnotmuch\user\models;

interface JWTSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey($asArray = false);

    /**
     * Returns a string to sign token
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

    /**
     * Allows to store a token for further processing.
     *
     * @param string $token
     */
    public function setAuthToken(string $token);

    /**
     * Returns recently created token.
     *
     * @return string
     */
    public function getAuthToken();

}
