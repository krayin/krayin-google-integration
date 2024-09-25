<?php

namespace Webkul\Google\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'activity.create.after' => [
            'Webkul\Google\Listeners\Activity@created',
        ],

        'activity.update.after' => [
            'Webkul\Google\Listeners\Activity@updated',
        ],

        'activity.delete.before' => [
            'Webkul\Google\Listeners\Activity@deleted',
        ],
    ];
}
