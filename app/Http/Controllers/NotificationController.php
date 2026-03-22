<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListNotificationsRequest;
use App\Http\Resources\NotificationResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(ListNotificationsRequest $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->notifications()->latest('created_at');

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        $perPage = $request->validated('per_page', 20);
        $notifications = $query->paginate($perPage);

        return $this->success(
            NotificationResource::collection($notifications)->response()->getData(true)
        );
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return $this->error('Notification not found', 404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return $this->success(null, 'Notification marked as read');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->unreadNotifications()->update(['read_at' => now()]);

        return $this->success(null, 'All notifications marked as read');
    }
}
