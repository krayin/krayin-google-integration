<?php

namespace Webkul\Google\Http\Controllers;

use Webkul\Google\Repositories\AccountRepository;

class CalendarController extends Controller
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
     *
     * @return void
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * Synchronize
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sync($id)
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

        $primaryCalendar->synchronization->ping();

        session()->flash('success', trans('google::app.sync-success'));

        return redirect()->back();
    }
}
