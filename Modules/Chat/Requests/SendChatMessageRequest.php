<?php

namespace Modules\Chat\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string', 'max:5000'],
            'message_type' => ['required', 'string', 'in:text,image,file,system'],
            'attachment' => ['nullable', 'file', 'max:' . config('chat.max_attachment_size')],
        ];
    }
}
