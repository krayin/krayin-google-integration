<?php

namespace Webkul\Google\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Google\Repositories\AccountRepository;
use Webkul\Google\Repositories\CalendarRepository;
use Webkul\Google\Services\Google;
use Webkul\User\Repositories\UserRepository;

class AccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     *
     * @return void
     */
    public function __construct(
        protected Google $google,
        protected UserRepository $userRepository,
        protected AccountRepository $accountRepository,
        protected CalendarRepository $calendarRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View|RedirectResponse
    {
        if (! request('route')) {
            return redirect()->route('admin.google.index', ['route' => 'calendar']);
        }

        $account = $this->accountRepository->findOneByField('user_id', auth()->user()->id);

        return view('google::'.request('route').'.index', compact('account'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): RedirectResponse
    {
        $account = $this->accountRepository->findOneByField('user_id', auth()->user()->id);

        if ($account) {
            $this->accountRepository->update([
                'scopes' => array_merge($account->scopes ?? [], [request('route')]),
            ], $account->id);

            if (request('route') == 'calendar') {
                $account->synchronization->ping();
                $account->synchronization->startListeningForChanges();
            }

            session()->put('route', request('route'));
        } else {
            if (! request()->has('code')) {
                session()->put('route', request('route'));

                return redirect($this->google->createAuthUrl());
            }

            $this->google->authenticate(request()->get('code'));

            $account = $this->google->service('Oauth2')->userinfo->get();

            $this->userRepository->find(auth()->user()->id)->accounts()->updateOrCreate(
                [
                    'google_id' => $account->id,
                ],
                [
                    'name'   => $account->email,
                    'token'  => $this->google->getAccessToken(),
                    'scopes' => [session()->get('route', 'calendar')],
                ]
            );
        }

        return redirect()->route('admin.google.index', ['route' => session()->get('route', 'calendar')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $account = $this->accountRepository->findOrFail($id);

        if (count($account->scopes) > 1) {
            $scopes = $account->scopes;

            if (($key = array_search(request('route'), $scopes)) !== false) {
                unset($scopes[$key]);
            }

            $this->accountRepository->update([
                'scopes' => array_values($scopes),
            ], $account->id);
        } else {
            $account->calendars->each->delete();

            $this->accountRepository->destroy($id);

            $this->google->revokeToken($account->token);
        }

        session()->flash('success', trans('google::app.account-deleted'));

        return redirect()->back();
    }
}
