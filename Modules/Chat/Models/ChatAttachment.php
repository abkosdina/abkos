<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Modules\Shared\Base\BaseModel;

class ChatAttachment extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'chat_attachments';

    protected $fillable = [
        'uuid',
        'chat_message_id',
        'file_path',
        'mime_type',
        'size_bytes',
        'created_by',
    ];

    protected $appends = [
        'file_url',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? Storage::disk(config('chat.attachment_disk'))->url($this->file_path) : null;
    }
}
