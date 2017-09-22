<?php

namespace beardedandnotmuch\user\helpers;

use Yii;
use Exception;
use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\Signer\Hmac\Sha256 as Signer;
use Lcobucci\JWT\Parser as JWTParser;
use Lcobucci\JWT\ValidationData;
use beardedandnotmuch\user\models\JWTSourceInterface;

class JWT
{
    /**
     * Generates JWT object.
     *
     * @param JWTSourceInterface $user
     * @param integer|bool $duration if "false" expiration will be disabled
     *
     * @return Lcobucci\JWT\Token;
     */
    public static function token(JWTSourceInterface $user, $duration = 3600)
    {
        $now = time();
        $request = Yii::$app->getRequest();

        $pk = $user->getPrimaryKey(true);
        $id = implode(',', $pk);

        $builder = (new JWTBuilder())
            ->setIssuer($request->hostInfo)
            ->setAudience($request->hostInfo)
            ->setId($id, true)
            ->setIssuedAt($now)
            ->setNotBefore($now)
        ;

        if ($duration !== false) {
            $builder->setExpiration($now + $duration);
        }

        foreach ($user->getTokenClaims() as $name => $value) {
            $builder->set($name, $value);
        }

        return $builder
            ->sign(new Signer(), $user->getSecretKey())
            ->getToken();
    }

    /**
     * undocumented function
     *
     * @param string $token
     *
     * @return bool
     */
    public static function isExpired($token)
    {
        $request = Yii::$app->getRequest();

        $token = (new JWTParser())->parse((string) $token);

        $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer($request->hostInfo);
        $data->setAudience($request->hostInfo);

        return !$token->validate($data);
    }
}
