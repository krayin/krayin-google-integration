<?php

namespace Webkul\Google\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Google\Concerns\Synchronizable;
use Webkul\Google\Contracts\Account as AccountContract;
use Webkul\Google\Jobs\SynchronizeCalendars;
use Webkul\Google\Jobs\WatchCalendars;

class Account extends Model implements AccountContract
{
    use Synchronizable;

    protected $table = 'google_accounts';

    protected $fillable = [
        'google_id',
        'name',
        'token',
        'scopes',
    ];

    protected $casts = [
        'token'  => 'json',
        'scopes' => 'json',
    ];

    /**
     * Get the user that owns the google account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the calendars.
     */
    public function calendars()
    {
        return $this->hasMany(CalendarProxy::modelClass(), 'google_account_id');
    }

    /**
     * Synchronize calendars.
     */
    public function synchronize()
    {
        SynchronizeCalendars::dispatch($this);
    }

    public function watch()
    {
        WatchCalendars::dispatch($this);
    }
}
