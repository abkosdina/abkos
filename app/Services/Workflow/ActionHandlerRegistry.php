<?php

namespace App\Services\Workflow;

use InvalidArgumentException;

class ActionHandlerRegistry
{
    private array $handlers = [];

    public function register(string $key, ActionHandlerInterface $handler): void
    {
        $safeKey = $this->normalizeKey($key);
        $this->handlers[$safeKey] = $handler;
    }

    public function resolve(string $key): ActionHandlerInterface
    {
        $safeKey = $this->normalizeKey($key);

        if (! isset($this->handlers[$safeKey])) {
            throw new InvalidArgumentException("Action handler [{$safeKey}] is not registered.");
        }

        return $this->handlers[$safeKey];
    }

    public function isRegistered(string $key): bool
    {
        return isset($this->handlers[$this->normalizeKey($key)]);
    }

    private function normalizeKey(string $key): string
    {
        return trim(strtolower($key));
    }
}
