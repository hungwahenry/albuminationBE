<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateNotificationPreferenceRequest;
use App\Models\UserNotificationPreference;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    use ApiResponse;

    public function show(Request $request): JsonResponse
    {
        $preferences = UserNotificationPreference::firstOrCreate(['user_id' => $request->user()->id]);

        return $this->success($preferences->toArray());
    }

    public function update(UpdateNotificationPreferenceRequest $request): JsonResponse
    {
        $preferences = UserNotificationPreference::firstOrCreate(['user_id' => $request->user()->id]);

        $preferences->fill($request->validated())->save();

        return $this->success($preferences->toArray());
    }
}
