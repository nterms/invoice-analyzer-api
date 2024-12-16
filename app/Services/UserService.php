<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    protected $userRepository;

    /**
     * Constructor
     * 
     * @param \App\Repositories\UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user
     * 
     * @param array $data User details
     */
    public function register(array $data): User
    {
        $data['password'] = bcrypt($data['password']);

        $user = $this->userRepository->create($data);

        return $user;
    }
}
