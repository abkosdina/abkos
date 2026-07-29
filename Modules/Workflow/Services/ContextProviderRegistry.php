<?php

namespace Modules\Workflow\Services;

class ContextProviderRegistry
{
    protected array $providers = [];

    public function register(string $name, callable $provider): self
    {
        $this->providers[$name] = $provider;

        return $this;
    }

    public function resolve(string $name, array $context = []): mixed
    {
        if (! isset($this->providers[$name])) {
            return null;
        }

        return ($this->providers[$name])($context);
    }
}
