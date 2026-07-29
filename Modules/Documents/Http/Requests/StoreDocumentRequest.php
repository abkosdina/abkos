<?php

namespace Modules\Documents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
            'category_id' => ['nullable', 'integer', 'exists:document_categories,id'],
            'owner_type' => ['required', 'string'],
            'owner_id' => ['required', 'integer'],
            'file' => ['required', 'file'],
            'visibility' => ['nullable', 'string', 'in:private,shared,workflow,public,internal'],
            'status' => ['nullable', 'string', 'in:uploading,uploaded,pending_approval,approved,rejected,archived,deleted,expired'],
        ];
    }
}
