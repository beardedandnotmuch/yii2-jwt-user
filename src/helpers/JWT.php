<?php

namespace beardedandnotmuch\user\helpers;

use Yii;
use Exception;
use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\Signer\Hmac\Sha256 as Signer;

class JWT
{
    /**
     * undocumented function
     *
     * @return string
     */
    public static function token($user)
    {
        $class = Yii::$app->getUser()->identityClass;

        if (!($user instanceof $class)) {
            throw new Exception("Argument should be instance of \"$class\"");
        }

        $now = time();
        $request = Yii::$app->getRequest();

        $token = (new JWTBuilder())
            ->setIssuer($request->hostInfo)
            ->setAudience($request->hostInfo)
            ->setId($user->id, true)
            ->setIssuedAt($now)
            ->setNotBefore($now)
            ->setExpiration($now + 3600)
            ->sign(new Signer(), $user->getSecretKey())
            ->getToken();

        return (string) $token;
    }
}
