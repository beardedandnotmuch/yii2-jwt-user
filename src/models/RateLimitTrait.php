<?php

namespace beardedandnotmuch\user\models;

use beardedandnotmuch\user\Module;

/**
 * Trait RateLimitTrait
 */
trait RateLimitTrait
{
    /**
     * {@inheritdoc}
     */
    public function getRateLimit($request, $action)
    {
        return [$this->rate_limit, Module::RATE_LIMIT_WINDOW];
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllowance($request, $action)
    {
        $value = null;
        if ($this->allowance_updated_at !== null) {
            $date = new \DateTime($this->allowance_updated_at, new \DateTimeZone('UTC'));
            $value = $date->format('U');
        }

        return [$this->allowance, $value];
    }

    /**
     * {@inheritdoc}
     */
    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $this->allowance = $allowance;
        $this->allowance_updated_at = gmdate('Y-m-d H:i:s', $timestamp);
        $this->save(true, ['allowance', 'allowance_updated_at']);
    }

}
