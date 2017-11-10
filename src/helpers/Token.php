<?php

namespace beardedandnotmuch\user\helpers;

use Base64Url\Base64Url;
use yii\helpers\Json;

class Token
{
    /**
     * Returns url-safe token.
     *
     * @return string
     */
    public static function encode(array $data)
    {
        return Base64Url::encode(Json::encode($data));
    }

    /**
     * Takes data from token.
     *
     * @return array
     */
    public static function decode($token)
    {
        return Json::decode(Base64Url::decode($token));
    }

}
