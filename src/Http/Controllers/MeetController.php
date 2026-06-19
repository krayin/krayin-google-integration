<?php

namespace Webkul\Google\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Webkul\Google\Repositories\AccountRepository;

class MeetController extends Controller
{
    /**
     * Create a new controller instance.
     *
     *
     * @return void
     */
    public function __construct(protected AccountRepository $accountRepository) {}

    /**
     * Create google meet link
     */
    public function createLink(): JsonResponse
    {
        $account = $this->accountRepository->findOneByField('user_id', auth()->user()->id);

        if (! $account) {
            return response()->json([
                'message' => trans('google::app.meet.index.account-not-found'),
            ], 404);
        }

        $service = $account->getGoogleService('Calendar');

        $start = request('schedule_from')
            ? Carbon::createFromFormat('Y-m-d H:i:s', request('schedule_from'))
            : Carbon::now();

        $end = request('schedule_to')
            ? Carbon::createFromFormat('Y-m-d H:i:s', request('schedule_to'))
            : Carbon::now()->addMinutes(30);

        $googleEvent = $service->events->insert(
            'primary',

            new \Google_Service_Calendar_Event([
                'summary'        => request('title'),

                'start'          => [
                    'dateTime' => $start->toAtomString(),
                    'timeZone' => $start->timezone->getName(),
                ],

                'end' => [
                    'dateTime' => $end->toAtomString(),
                    'timeZone' => $end->timezone->getName(),
                ],

                'conferenceData' => [
                    'createRequest' => [
                        'conferenceSolutionKey' => [
                            'type' => 'hangoutsMeet',
                        ],

                        'requestId' => 'meet_'.time(),
                    ],
                ],
            ]),

            ['conferenceDataVersion' => 1]
        );

        $service->events->delete('primary', $googleEvent->id);

        return response()->json([
            'link'    => $googleEvent->hangoutLink,
            'comment' => trans('google::app.meet.index.link-shared', ['link' => $googleEvent->hangoutLink]),
        ]);
    }
}
