<?php

namespace Webkul\Google\Concerns;

use Webkul\Google\Models\SynchronizationProxy;
use Webkul\Google\Services\Google;

trait Synchronizable
{
    /**
     * Boot the synchronizable trait for a model.
     */
    public static function bootSynchronizable(): void
    {
        /**
         * Start a new synchronization once created.
         */
        static::created(fn ($synchronizable) => $synchronizable->synchronization()->create());

        /**
         * Stop and delete associated synchronization.
         */
        static::deleting(fn ($synchronizable) => optional($synchronizable->synchronization)->delete());
    }

    /**
     * Get the synchronization record associated with the synchronizable model.
     */
    public function synchronization()
    {
        return $this->morphOne(SynchronizationProxy::modelClass(), 'synchronizable');
    }

    /**
     * Get the Google service instance.
     */
    public function getGoogleService(mixed $service): mixed
    {
        return app(Google::class)
            ->connectWithSynchronizable($this)
            ->service($service);
    }

    abstract public function synchronize();

    abstract public function watch();
}
