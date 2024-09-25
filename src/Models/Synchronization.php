<?php

namespace Webkul\Google\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Webkul\Google\Contracts\Synchronization as SynchronizationContract;

class Synchronization extends Model implements SynchronizationContract
{
    /**
     * Define the table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_synchronizations';

    /**
     * Default incrementing value false.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Define the fillable property.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'last_synchronized_at',
        'resource_id',
        'expired_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_synchronized_at' => 'datetime',
        'expired_at'           => 'datetime',
    ];

    /**
     * Get the synchronizable instance.
     */
    public function ping(): mixed
    {
        return $this->synchronizable->synchronize();
    }

    /**
     * Start listening for changes.
     */
    public function startListeningForChanges(): mixed
    {
        return $this->synchronizable->watch();
    }

    /**
     * Stop Listening for changes.
     *
     * @return void
     */
    public function stopListeningForChanges()
    {
        if (! $this->resource_id) {
            return;
        }

        $this->synchronizable
            ->getGoogleService('Calendar')
            ->channels->stop($this->asGoogleChannel());
    }

    /**
     * Get the synchronizable instance.
     *
     * @return void
     */
    public function synchronizable()
    {
        return $this->morphTo();
    }

    /**
     * Refresh webhook.
     */
    public function refreshWebhook(): self
    {
        $this->stopListeningForChanges();

        // Update the UUID since the previous one has
        // already been associated to a Google Channel.
        $this->id = Uuid::uuid4();
        $this->save();

        $this->startListeningForChanges();

        return $this;
    }

    /**
     * Get the Google Channel.
     */
    public function asGoogleChannel(): mixed
    {
        return tap(new \Google_Service_Calendar_Channel, function ($channel) {
            $channel->setId($this->id);
            $channel->setResourceId($this->resource_id);
            $channel->setType('web_hook');
            $channel->setAddress(config('services.google.webhook_uri'));
        });
    }

    /**
     * Boot the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($synchronization) {
            $synchronization->id = Uuid::uuid4();

            $synchronization->last_synchronized_at = now();
        });

        static::created(function ($synchronization) {
            $synchronization->startListeningForChanges();

            $synchronization->ping();
        });

        static::deleting(function ($synchronization) {
            $synchronization->stopListeningForChanges();
        });
    }
}
