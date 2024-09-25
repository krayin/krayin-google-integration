<?php

namespace Webkul\Google\Listeners;

use Webkul\Activity\Contracts\Activity as ActivityContract;
use Webkul\Google\Repositories\AccountRepository;
use Webkul\Google\Repositories\CalendarRepository;
use Webkul\Google\Repositories\EventRepository;

class Activity
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected AccountRepository $accountRepository,
        protected CalendarRepository $calendarRepository,
        protected EventRepository $eventRepository
    ) {}

    /**
     * Handle the created event.
     *
     * @return void
     */
    public function created(ActivityContract $activity)
    {
        if (! in_array($activity->type, ['call', 'meeting', 'lunch'])) {
            return;
        }

        $account = $this->accountRepository->findOneByField('user_id', auth()->user()->id);

        if (! $account) {
            return;
        }

        $calendar = $this->calendarRepository->findOneWhere([
            'google_account_id' => $account->id,
            'is_primary'        => 1,
        ]);

        if (! $calendar) {
            return;
        }

        $service = $calendar->getGoogleService('Calendar');

        $eventData = [
            'summary'     => $activity->title,
            'description' => $activity->comment,
            'start'       => [
                'dateTime' => $activity->schedule_from->toAtomString(),
                'timeZone' => $activity->schedule_from->timezone->getName(),
            ],
            'end' => [
                'dateTime' => $activity->schedule_to->toAtomString(),
                'timeZone' => $activity->schedule_from->timezone->getName(),
            ],
        ];

        foreach ($activity->participants as $participant) {
            if ($participant->user) {
                $eventData['attendees'][] = ['email' => $participant->user->email, 'display_name' => $participant->user->name];
            } else {
                $eventData['attendees'][] = ['email' => $participant->person->emails[0]['value'], 'display_name' => $participant->person->name];
            }
        }

        $googleEvent = $service->events->insert(
            $calendar->google_id,
            new \Google_Service_Calendar_Event($eventData)
        );

        $this->eventRepository->create([
            'activity_id'        => $activity->id,
            'google_id'          => $googleEvent->id,
            'google_calendar_id' => $calendar->id,
        ]);
    }

    /**
     * Handle the updated event.
     *
     * @return void
     */
    public function updated(ActivityContract $activity)
    {
        if (! in_array($activity->type, ['call', 'meeting', 'lunch'])) {
            return;
        }

        $account = $this->accountRepository->findOneByField('user_id', auth()->user()->id);

        if (! $account) {
            return;
        }

        $event = $this->eventRepository->findOneByField('activity_id', $activity->id);

        if (! $event || ! $calendar = $event->calendar) {
            $calendar = $this->calendarRepository->findOneWhere([
                'google_account_id' => $account->id,
                'is_primary'        => 1,
            ]);
        }

        if (! $calendar) {
            return;
        }

        $service = $calendar->getGoogleService('Calendar');

        $eventData = [
            'summary'     => $activity->title,
            'description' => $activity->comment,
            'start'       => [
                'dateTime' => $activity->schedule_from->toAtomString(),
                'timeZone' => $activity->schedule_from->timezone->getName(),
            ],
            'end' => [
                'dateTime' => $activity->schedule_to->toAtomString(),
                'timeZone' => $activity->schedule_from->timezone->getName(),
            ],
        ];

        foreach ($activity->participants as $participant) {
            if ($participant->user) {
                $eventData['attendees'][] = ['email' => $participant->user->email, 'display_name' => $participant->user->name];
            } else {
                $eventData['attendees'][] = ['email' => $participant->person->emails[0]['value'], 'display_name' => $participant->person->name];
            }
        }

        if ($event?->google_id) {
            $googleEvent = $service->events->update(
                $calendar->google_id,
                $event->google_id,
                new \Google_Service_Calendar_Event($eventData)
            );
        } else {
            $googleEvent = $service->events->insert(
                $calendar->google_id,
                new \Google_Service_Calendar_Event($eventData)
            );
        }

        $this->eventRepository->updateOrCreate([
            'activity_id' => $activity->id,
        ], [
            'google_id'          => $googleEvent->id,
            'google_calendar_id' => $calendar->id,
        ]
        );
    }

    /**
     * Handle the deleted event.
     *
     * @return void
     */
    public function deleted(int $id)
    {
        $event = $this->eventRepository->findOneByField('activity_id', $id);

        if (! $event) {
            return;
        }

        $service = $event->calendar->getGoogleService('Calendar');

        $service->events->delete($event->calendar->google_id, $event->google_id);
    }
}
