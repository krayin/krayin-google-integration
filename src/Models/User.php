<?php

namespace Webkul\Google\Models;

use Webkul\Activity\Models\Activity;
use Webkul\User\Models\User as BaseUser;

class User extends BaseUser
{
    /**
     * Get the google accounts.
     */
    public function accounts()
    {
        return $this->hasMany(AccountProxy::modelClass());
    }

    /**
     * Get the events.
     */
    public function events()
    {
        return Activity::whereHas('calendar', function ($calendarQuery) {
            $calendarQuery->whereHas('account', function ($accountQuery) {
                $accountQuery->whereHas('user', function ($userQuery) {
                    $userQuery->where('id', $this->id);
                });
            });
        });
    }
}
