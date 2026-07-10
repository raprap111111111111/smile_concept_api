<?php

namespace App\Http\Controllers\v1;

use App\Domain\Notifications\Actions\MarkAllAsReadAction;
use App\Domain\Notifications\Actions\MarkAsReadAction;
use App\Domain\Notifications\Repositories\NotificationRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Notification\GetAllNotificationsRequest;
use App\Http\Resources\v1\NotificationResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly NotificationRepository $repository,
        private readonly MarkAsReadAction       $markAsReadAction,
        private readonly MarkAllAsReadAction    $markAllAsReadAction,
    ) {}

    /**
     * List current user's notifications.
     */
    public function index(GetAllNotificationsRequest $request): JsonResponse
    {
        $result = $this->repository->paginateForUser(
            Auth::user(),
            $request->validated()
        );

        $result['data'] = NotificationResource::collection($result['data']);

        return $this->successResponse($result, 'Notifications retrieved.');
    }

    /**
     * Show unread count for bell icon badge.
     */
    public function unreadCount(): JsonResponse
    {
        return $this->successResponse(
            ['count' => $this->repository->unreadCount(Auth::user())],
            'Unread count retrieved.'
        );
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $success = $this->markAsReadAction->execute(Auth::user(), $id);

        if (!$success) {
            return $this->errorResponse('Notification not found.', 404);
        }

        return $this->successResponse(null, 'Notification marked as read.');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = $this->markAllAsReadAction->execute(Auth::user());

        return $this->successResponse(
            ['marked' => $count],
            "Marked {$count} notification(s) as read."
        );
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id): JsonResponse
    {
        $notification = $this->repository->findForUser(Auth::user(), $id);

        if (!$notification) {
            return $this->errorResponse('Notification not found.', 404);
        }

        $notification->delete();

        return $this->successResponse(null, 'Notification deleted.');
    }
}