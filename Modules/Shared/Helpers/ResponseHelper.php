<?php

namespace Modules\Shared\Helpers;

class ResponseHelper
{
    public static function success(mixed $data = null, string $message = 'Success', int $status = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    public static function error(string $message = 'Something went wrong', int $status = 500, mixed $data = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];
    }
}
