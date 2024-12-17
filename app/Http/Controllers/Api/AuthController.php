<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Services\UserService;
use Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /** @var \App\Services\UserService $userService */
    protected $userService;

    /**
     * @param \App\Services\UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function signup(SignupRequest $request)
    {
        $data = $request->validated();
        
        $user = $this->userService->register($data);
        $token = $user->createToken('main')->plainTextToken;

        return response(compact('user', 'token'));
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response([
                'message' => 'Provided email address of password is incorrect.',
            ], 422);
        }

        $user = Auth::user();
        $token = $user->createToken('main')->plainTextToken;

        return response(compact('user', 'token'));
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response('', 204);
    }
}
