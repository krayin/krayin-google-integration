<?php

namespace Webkul\Google\Providers;

use Webkul\Google\Models\Account;
use Webkul\Google\Models\Calendar;
use Webkul\Google\Models\Event;
use Webkul\Google\Models\Synchronization;
use Webkul\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Account::class,
        Calendar::class,
        Event::class,
        Synchronization::class,
    ];
}
