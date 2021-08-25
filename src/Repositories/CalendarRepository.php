<?php

namespace Webkul\Google\Repositories;

use Webkul\Core\Eloquent\Repository;

class CalendarRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Google\Contracts\Calendar';
    }
}