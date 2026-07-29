<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Base\BaseModel;

class ChatMessageRead extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'chat_message_reads';

    protected $fillable = [
        'uuid',
        'chat_message_id',
        'user_id',
        'read_at',
        'created_by',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
