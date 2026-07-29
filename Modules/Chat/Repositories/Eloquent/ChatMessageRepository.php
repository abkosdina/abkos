<?php

namespace Modules\Chat\Repositories\Eloquent;

use Modules\Chat\Models\ChatMessage;
use Modules\Chat\Repositories\Interfaces\ChatMessageRepositoryInterface;

class ChatMessageRepository implements ChatMessageRepositoryInterface
{
    public function __construct(protected ChatMessage $model)
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

    public function getByRoom(int|string $roomId, array $columns = ['*']): array
    {
        return $this->model->query()
            ->where('chat_room_id', $roomId)
            ->with(['sender', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->get($columns)
            ->all();
    }
}
