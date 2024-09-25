<?php

namespace Webkul\Google\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class SynchronizeEvents extends SynchronizeResource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Get the google request.
     */
    public function getGoogleRequest(mixed $service, mixed $options): mixed
    {
        return $service->events->listEvents(
            $this->synchronizable->google_id, $options
        );
    }

    /**
     * Sync Item.
     */
    public function syncItem($googleEvent)
    {
        if ($googleEvent->status === 'cancelled') {
            return $this->synchronizable->events()
                ->where('google_id', $googleEvent->id)
                ->delete();
        }

        if (Carbon::now() > $this->parseDatetime($googleEvent->start)) {
            return;
        }

        $event = $this->synchronizable->events()->updateOrCreate([
            'google_id' => $googleEvent->id,
        ]);

        $activity = $event->activity()->updateOrCreate(
            [
                'id' => $event->activity_id,
            ], [
                'title'         => $googleEvent->summary,
                'comment'       => $googleEvent->description,
                'schedule_from' => $this->parseDatetime($googleEvent->start),
                'schedule_to'   => $this->parseDatetime($googleEvent->end),
                'user_id'       => $this->synchronizable->account->user_id,
                'type'          => 'meeting',
            ]
        );

        $event->update(['activity_id' => $activity->id]);
    }

    /**
     * Drop all synced items.
     */
    public function dropAllSyncedItems()
    {
        $this->synchronizable->events()->delete();
    }

    /**
     * Check if the event is all day event.
     */
    protected function isAllDayEvent($googleEvent)
    {
        return ! $googleEvent->start->dateTime && ! $googleEvent->end->dateTime;
    }

    /**
     * Parse datetime.
     */
    protected function parseDatetime($googleDatetime)
    {
        $rawDatetime = $googleDatetime->dateTime ?: $googleDatetime->date;

        return Carbon::parse($rawDatetime);
    }
}
