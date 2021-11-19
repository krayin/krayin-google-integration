<?php

namespace Webkul\Google\Http\Controllers;

use Carbon\Carbon;
use Webkul\Google\Repositories\AccountRepository;

class MeetController extends Controller
{
    /**
     * AccountRepository object
     *
     * @var \Webkul\Repositories\Services\AccountRepository
     */
    protected $accountRepository;

    /**
     * Create a new controller instance.
     *
     * @param \Webkul\Google\Repositories\AccountRepository  $accountRepository
     *
     * @return void
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * Create google meet link
     * 
     * @return \Illuminate\Http\Response
     */
    public function createLink()
    {
        $account = $this->accountRepository->first();

        $service = $account->getGoogleService('Calendar');

        $start = request('schedule_from')
            ? Carbon::createFromFormat('Y-m-d H:i:s', request('schedule_from'))
            : Carbon::now();

        $end = request('schedule_ro')
            ? Carbon::createFromFormat('Y-m-d H:i:s', request('schedule_ro'))
            : Carbon::now()->addMinutes(30);

        $googleEvent = $service->events->insert(
            'primary',

            new \Google_Service_Calendar_Event([
                'summary'        => 'Google Meet Link',

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

                        'requestId' => 'meet_' . time(),
                    ],
                ],
            ]),
            
            ['conferenceDataVersion' => 1]
        );

        $service->events->delete('primary', $googleEvent->id);

        return response()->json([
            'link'    => $googleEvent->hangoutLink,
            'comment' => '──────────<br/><br/>You are invited to join Google Meet meeting.<br/><br/>Join the Google Meet meeting: <a href="' . $googleEvent->hangoutLink . '" target="_blank">' . $googleEvent->hangoutLink . '</a><br/><br/>──────────'
        ]);
    }
}
