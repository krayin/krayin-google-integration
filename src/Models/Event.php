<?php

namespace Webkul\Google\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Activity\Models\ActivityProxy;
use Webkul\Google\Contracts\Event as EventContract;

class Event extends Model implements EventContract
{
    /**
     * Timestamps disabled.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Define the table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_events';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'google_id',
        'google_calendar_id',
        'activity_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $with = ['calendar'];

    /**
     * Get the activity that owns the event.
     */
    public function activity()
    {
        return $this->belongsTo(ActivityProxy::modelClass());
    }

    /**
     * Get the calendar that owns the activity.
     */
    public function calendar()
    {
        return $this->belongsTo(CalendarProxy::modelClass(), 'google_calendar_id');
    }
}
