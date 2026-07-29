<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class ChatParticipant extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'chat_participants';

    protected $fillable = [
        'uuid',
        'chat_room_id',
        'user_id',
        'role',
        'joined_at',
        'created_by',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
