<?php

namespace beardedandnotmuch\user\helpers;

use Yii;
use Exception;
use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\Signer\Hmac\Sha256 as Signer;
use beardedandnotmuch\user\models\JWTSourceInterface;

class JWT
{
    /**
     * undocumented function
     *
     * @return Lcobucci\JWT\Token;
     */
    public static function token(JWTSourceInterface $user, $duration = 3600)
    {
        $now = time();
        $request = Yii::$app->getRequest();

        $pk = $user->getPrimaryKey(true);
        $id = implode(',', $pk);

        return (new JWTBuilder())
            ->setIssuer($request->hostInfo)
            ->setAudience($request->hostInfo)
            ->setId($id, true)
            ->setIssuedAt($now)
            ->setNotBefore($now)
            ->setExpiration($now + $duration)
            ->sign(new Signer(), $user->getSecretKey())
            ->getToken();
    }
}
