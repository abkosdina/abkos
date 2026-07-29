<?php

namespace Modules\Chat\Repositories\Eloquent;

use Modules\Chat\Models\ChatMessageRead;
use Modules\Chat\Repositories\Interfaces\ChatMessageReadRepositoryInterface;

class ChatMessageReadRepository implements ChatMessageReadRepositoryInterface
{
    public function __construct(protected ChatMessageRead $model)
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

    public function markAsRead(int|string $messageId, int|string $userId): object
    {
        $existing = $this->getByMessageAndUser($messageId, $userId);

        if ($existing) {
            $this->model->query()->find($existing->id)->update(['read_at' => now()]);
            return $this->model->query()->find($existing->id);
        }

        return $this->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'chat_message_id' => $messageId,
            'user_id' => $userId,
            'read_at' => now(),
        ]);
    }

    public function getByMessageAndUser(int|string $messageId, int|string $userId, array $columns = ['*']): ?object
    {
        return $this->model->query()->where('chat_message_id', $messageId)->where('user_id', $userId)->first($columns);
    }
}
