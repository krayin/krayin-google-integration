<?php

namespace Webkul\Google\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Webkul\Google\Repositories\AccountRepository;

class CalendarController extends Controller
{
    /**
     * Create a new controller instance.
     *
     *
     * @return void
     */
    public function __construct(protected AccountRepository $accountRepository) {}

    /**
     * Synchronize.
     */
    public function sync(int $id): RedirectResponse
    {
        $account = $this->accountRepository->findOrFail($id);

        $primaryCalendar = null;

        foreach ($account->calendars as $calendar) {
            if ($calendar->id == request('calendar_id')) {
                $calendar->update(['is_primary' => 1]);

                $primaryCalendar = $calendar;
            } else {
                $calendar->update(['is_primary' => 0]);
            }
        }

        $primaryCalendar?->synchronization->ping();

        session()->flash('success', trans('google::app.account-synced'));

        return redirect()->back();
    }
}
