<?php

namespace App\Http\Controllers;

use App\Http\Resources\BadgeResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    use ApiResponse;

    public function mine(Request $request): JsonResponse
    {
        $badges = $request->user()->badges()->get();

        return $this->success(BadgeResource::collection($badges));
    }

    public function forUser(string $username): JsonResponse
    {
        $user = User::whereHas('profile', fn ($q) => $q->where('username', $username))->first();

        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->success(BadgeResource::collection($user->badges()->get()));
    }
}
