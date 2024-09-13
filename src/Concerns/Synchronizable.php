<?php

namespace Webkul\Google\Concerns;

use Webkul\Google\Models\SynchronizationProxy;
use Webkul\Google\Services\Google;

trait Synchronizable
{
    public static function bootSynchronizable()
    {
        // Start a new synchronization once created.
        static::created(function ($synchronizable) {
            $synchronizable->synchronization()->create();
        });

        // Stop and delete associated synchronization.
        static::deleting(function ($synchronizable) {
            optional($synchronizable->synchronization)->delete();
        });
    }

    public function synchronization()
    {
        return $this->morphOne(SynchronizationProxy::modelClass(), 'synchronizable');
    }

    public function getGoogleService($service)
    {
        return app(Google::class)
            ->connectWithSynchronizable($this)
            ->service($service);
    }

    abstract public function synchronize();

    abstract public function watch();
}
