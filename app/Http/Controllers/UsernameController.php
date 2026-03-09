<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsernameController extends Controller
{
    use ApiResponse;

    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'min:4', 'max:32', 'regex:/^[a-zA-Z0-9_]+$/'],
        ]);

        $username = strtolower($request->username);
        $exists = Profile::where('username', $username)->exists();

        return $this->success([
            'available' => !$exists,
        ]);
    }
}
