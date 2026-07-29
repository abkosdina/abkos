<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class ChatRoom extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'chat_rooms';

    protected $fillable = [
        'uuid',
        'name',
        'room_type',
        'status',
        'created_by',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_room_id');
    }

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class, 'chat_room_id');
    }
}
