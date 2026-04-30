<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Admin\AdminLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    /**
     * Authenticate an admin and issue a Sanctum token.
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return $this->error('Invalid credentials. Please check your email and password.', 401);
        }

        /** @var \App\Models\Admin $admin */
        $admin = Auth::user();

        $token = $admin->createToken('admin-token')->plainTextToken;

        return $this->success([
            'token'      => $token,
            'token_type' => 'Bearer',
            'admin'      => [
                'id'            => $admin->id,
                'name'          => $admin->name,
                'email'         => $admin->email,
                'organizer_id'  => $admin->organizer_id,
            ],
        ], 'Login successful.');
    }

    /**
     * Revoke the current admin's token (logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }
}
