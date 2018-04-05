<?php

namespace beardedandnotmuch\user\models;

use yii\filters\RateLimitInterface;

/**
 * Simple user.
 */
class User extends BaseUser implements RateLimitInterface
{
    use RateLimitTrait;

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();
        // remove fields that contain sensitive information
        unset(
            $fields['allowance'],
            $fields['rate_limit'],
            $fields['allowance_updated_at']
        );

        return $fields;
    }

}
