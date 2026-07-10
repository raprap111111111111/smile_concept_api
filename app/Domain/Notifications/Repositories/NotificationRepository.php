<?php

namespace App\Domain\Notifications\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository
{
    public function paginateForUser(User $user, array $filters = []): array
    {
        $query = $user->notifications();

        if (!empty($filters['unread_only'])) {
            $query->whereNull('read_at');
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $total  = (clone $query)->count();
        $limit  = (int) ($filters['limit']  ?? 20);
        $offset = (int) ($filters['offset'] ?? 0);

        $items = $query->orderByDesc('created_at')
                       ->offset($offset)
                       ->limit($limit)
                       ->get();

        return [
            'data'   => $items,
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset,
        ];
    }

    public function findForUser(User $user, string $id): ?DatabaseNotification
    {
        return $user->notifications()->where('id', $id)->first();
    }

    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }
}