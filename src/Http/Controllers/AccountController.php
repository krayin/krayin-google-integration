<?php

namespace Webkul\Google\Http\Controllers;

use Webkul\Google\Services\Google;
use Webkul\User\Repositories\UserRepository;
use Webkul\Google\Repositories\AccountRepository;
use Webkul\Google\Repositories\CalendarRepository;

class AccountController extends Controller
{
    /**
     * Google object
     *
     * @var \Webkul\Google\Services\Google
     */
    protected $google;

    /**
     * UserRepository object
     *
     * @var \Webkul\Repositories\Services\UserRepository
     */
    protected $userRepository;

    /**
     * AccountRepository object
     *
     * @var \Webkul\Repositories\Services\AccountRepository
     */
    protected $accountRepository;

    /**
     * CalendarRepository object
     *
     * @var \Webkul\Repositories\Services\CalendarRepository
     */
    protected $calendarRepository;

    /**
     * Create a new controller instance.
     *
     * @param \Webkul\Google\Services\Google  $google
     * @param \Webkul\User\Repositories\UserRepository  $userRepository
     * @param \Webkul\Google\Repositories\AccountRepository  $accountRepository
     * @param \Webkul\Google\Repositories\CalendarRepository  $calendarRepository
     *
     * @return void
     */
    public function __construct(
        Google $google,
        UserRepository $userRepository,
        AccountRepository $accountRepository,
        CalendarRepository $calendarRepository
    )
    {
        $this->google = $google;

        $this->accountRepository = $accountRepository;

        $this->userRepository = $userRepository;

        $this->calendarRepository = $calendarRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $account = $this->accountRepository->findOneByField('user_id', auth()->user()->id);

        return view('google::calendar.index', compact('account'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        if (! request()->has('code')) {
            return redirect($this->google->createAuthUrl());
        }

        $this->google->authenticate(request()->get('code'));
        
        $account = $this->google->service('Oauth2')->userinfo->get();

        $this->userRepository->find(auth()->user()->id)->accounts()->updateOrCreate(
            [
                'google_id' => $account->id,
            ],
            [
                'name'  => $account->email,
                'token' => $this->google->getAccessToken(),
            ]
        );
    
        return redirect()->route('admin.google.index');
    }

    /**
     * Synchronize
     * 
     * @param  integer  $id
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $account = $this->accountRepository->findOrFail($id);

        $account->calendars->each->delete();

        $this->accountRepository->destroy($id);

        $this->google->revokeToken($account->token);

        session()->flash('success', trans('google::app.destroy-success'));

        return redirect()->back();
    }
}
