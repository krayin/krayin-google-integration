<?php

namespace Webkul\Google\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Google\Concerns\Synchronizable;
use Webkul\Google\Contracts\Calendar as CalendarContract;
use Webkul\Google\Jobs\SynchronizeEvents;
use Webkul\Google\Jobs\WatchEvents;

class Calendar extends Model implements CalendarContract
{
    use Synchronizable;

    /**
     * Define the table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_calendars';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'google_id',
        'name',
        'color',
        'timezone',
        'is_primary',
    ];

    /**
     * Get the google account that owns the calendar.
     */
    public function account()
    {
        return $this->belongsTo(AccountProxy::modelClass(), 'google_account_id');
    }

    /**
     * Get the events.
     */
    public function events()
    {
        return $this->hasMany(EventProxy::modelClass(), 'google_calendar_id');
    }

    /**
     * Synchronize events.
     */
    public function synchronize()
    {
        if (! $this->is_primary) {
            return;
        }

        SynchronizeEvents::dispatch($this);
    }

    /**
     * Watch the calendar.
     */
    public function watch()
    {
        if (! $this->is_primary) {
            return;
        }

        WatchEvents::dispatch($this);
    }
}
