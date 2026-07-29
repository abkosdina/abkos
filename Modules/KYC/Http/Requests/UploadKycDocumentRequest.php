<?php

namespace Modules\KYC\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadKycDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'document_type' => [
                'required',
                'string',
                'in:' . implode(',', config('kyc.required_documents', [])),
            ],
            'file' => [
                'required',
                'file',
                'max:' . (config('kyc.max_document_size', 10 * 1024 * 1024) / 1024),
                'mimes:pdf,jpeg,jpg,png',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document_type.required' => 'Document type is required.',
            'document_type.in' => 'Invalid document type.',
            'file.required' => 'File is required.',
            'file.file' => 'Uploaded file must be a valid file.',
            'file.max' => 'File size exceeds maximum allowed size.',
            'file.mimes' => 'File must be a PDF, JPEG, or PNG image.',
        ];
    }
}
