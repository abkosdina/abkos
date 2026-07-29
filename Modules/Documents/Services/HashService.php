<?php

namespace Modules\Documents\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class HashService
{
    public function generateUuid(): string
    {
        return Str::uuid()->toString();
    }

    public function generateFileHash(UploadedFile|string $file): string
    {
        if ($file instanceof UploadedFile) {
            return hash_file('sha256', $file->getRealPath());
        }

        return hash('sha256', file_get_contents($file));
    }
}
