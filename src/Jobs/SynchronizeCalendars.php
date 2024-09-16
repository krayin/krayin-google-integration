<?php

namespace Webkul\Google\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SynchronizeCalendars extends SynchronizeResource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Get the google request.
     */
    public function getGoogleRequest(mixed $service, mixed $options): mixed
    {
        return $service->calendarList->listCalendarList($options);
    }

    /**
     * Get the google request options.
     */
    public function syncItem($googleCalendar)
    {
        if ($googleCalendar->deleted) {
            return $this->synchronizable->calendars()
                ->where('google_id', $googleCalendar->id)
                ->get()->each->delete();
        }

        if ($googleCalendar->accessRole != 'owner') {
            return;
        }

        $this->synchronizable->calendars()->updateOrCreate(
            [
                'google_id' => $googleCalendar->id,
            ], [
                'name'     => $googleCalendar->summary,
                'color'    => $googleCalendar->backgroundColor,
                'timezone' => $googleCalendar->timeZone,
            ]
        );
    }

    /**
     * Drop all synced items.
     */
    public function dropAllSyncedItems()
    {
        $this->synchronizable->calendars->each->delete();
    }
}
