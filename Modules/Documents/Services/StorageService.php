<?php

namespace Modules\Documents\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StorageService
{
    protected string $driver;

    public function __construct(string $driver = 'local')
    {
        $this->driver = $driver;
    }

    public function disk(): Filesystem
    {
        $disk = config("documents.storage.disk_map.{$this->driver}") ?? $this->driver;

        return Storage::disk($disk);
    }

    public function putFile(string $directory, UploadedFile $file, string $filename = null): string
    {
        $filename = $filename ?? $file->hashName();

        return $this->disk()->putFileAs($directory, $file, $filename);
    }

    public function delete(string $path): bool
    {
        return $this->disk()->delete($path);
    }

    public function exists(string $path): bool
    {
        return $this->disk()->exists($path);
    }

    public function url(string $path): string
    {
        return $this->disk()->url($path);
    }

    public function getDriver(): string
    {
        return $this->driver;
    }
}
