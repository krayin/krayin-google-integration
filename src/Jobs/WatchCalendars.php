<?php

namespace Webkul\Google\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WatchCalendars extends WatchResource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The synchronizable instance.
     *
     * @param  mixed  $service
     * @param  mixed  $channel
     * @return mixed
     */
    public function getGoogleRequest($service, $channel)
    {
        return $service->calendarList->watch($channel);
    }
}
