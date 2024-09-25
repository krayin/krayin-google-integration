<?php

namespace Webkul\Google\Repositories;

use Webkul\Core\Eloquent\Repository;

class AccountRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'Webkul\Google\Contracts\Account';
    }
}
