<?php

namespace App\Services\Workflow;

class ActionResult
{
    public bool $success;
    public ?array $data;
    public string $message;
    public ?string $errorCode;
    public bool $retryable;
    public array $metadata;

    private function __construct(bool $success, array $data = null, string $message = '', ?string $errorCode = null, bool $retryable = false, array $metadata = [])
    {
        $this->success = $success;
        $this->data = $data;
        $this->message = $message;
        $this->errorCode = $errorCode;
        $this->retryable = $retryable;
        $this->metadata = $metadata;
    }

    public static function success(array $data = [], string $message = 'Action completed', array $metadata = []): self
    {
        return new self(true, $data, $message, null, false, $metadata);
    }

    public static function failure(string $errorCode, string $message = 'Action failed', bool $retryable = false, array $metadata = []): self
    {
        return new self(false, null, $message, $errorCode, $retryable, $metadata);
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'error_code' => $this->errorCode,
            'retryable' => $this->retryable,
            'metadata' => $this->metadata,
        ];
    }
}
