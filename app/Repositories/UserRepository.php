<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Traits\Repository;
use Illuminate\Database\Eloquent\Model;

class UserRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Model
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(User $user)
    {
        $this->model = $user;
    }
}
