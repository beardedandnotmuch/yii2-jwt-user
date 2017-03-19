<?php

namespace beardedandnotmuch\user\helpers;

class Url
{
    /**
     * Helper method for backward work of parse_url.
     *
     * NOTE: ensure that fragment comes AFTER querystring for proper $location
     * parsing using AngularJS.
     *
     * @return string
     */
    public static function generateUrl($url, $params)
    {
        $uri = array_merge([
            'scheme' => 'http',
            'port' => 80,
            'path' => '',
            'fragment' => '',
        ], parse_url($url));

        $res = "{$uri['scheme']}://{$uri['host']}";
        $res .= !in_array($uri['port'], [80, 443]) ? ":{$uri['port']}" : '';
        $res .= "{$uri['path']}#{$uri['fragment']}?";
        $res .= http_build_query($params);

        return $res;
    }
}
