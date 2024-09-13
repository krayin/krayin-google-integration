<?php

namespace Webkul\Google\Providers;

use Webkul\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Google\Models\Account::class,
        \Webkul\Google\Models\Calendar::class,
        \Webkul\Google\Models\Event::class,
        \Webkul\Google\Models\Synchronization::class,
    ];
}
