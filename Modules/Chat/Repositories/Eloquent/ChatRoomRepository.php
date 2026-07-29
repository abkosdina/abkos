<?php

namespace Modules\Chat\Repositories\Eloquent;

use Modules\Chat\Models\ChatRoom;
use Modules\Chat\Repositories\Interfaces\ChatRoomRepositoryInterface;

class ChatRoomRepository implements ChatRoomRepositoryInterface
{
    public function __construct(protected ChatRoom $model)
    {
    }

    public function all(array $columns = ['*']): array
    {
        return $this->model->query()->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return $this->model->query()->find($id, $columns);
    }

    public function create(array $data): object
    {
        return $this->model->query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return $this->model->query()->findOrFail($id)->update($data);
    }

    public function delete(int|string $id): bool
    {
        return $this->model->query()->findOrFail($id)->delete();
    }

    public function findByUuid(string $uuid, array $columns = ['*']): ?object
    {
        return $this->model->query()->where('uuid', $uuid)->first($columns);
    }

    public function findWithRelations(int|string $id, array $columns = ['*']): ?object
    {
        return $this->model->query()
            ->with(['participants.user', 'messages.sender', 'messages.attachments'])
            ->find($id, $columns);
    }

    public function getForUser(int|string $userId, array $columns = ['*']): array
    {
        return $this->model->query()
            ->whereHas('participants', fn ($query) => $query->where('user_id', $userId))
            ->with(['participants.user', 'messages' => fn ($query) => $query->latest()->limit(1)])
            ->orderByDesc('updated_at')
            ->get($columns)
            ->all();
    }

    public function getArchivedForUser(int|string $userId, array $columns = ['*']): array
    {
        return $this->model->query()
            ->where('status', 'archived')
            ->whereHas('participants', fn ($query) => $query->where('user_id', $userId))
            ->with(['participants.user', 'messages' => fn ($query) => $query->latest()->limit(1)])
            ->orderByDesc('updated_at')
            ->get($columns)
            ->all();
    }
}
