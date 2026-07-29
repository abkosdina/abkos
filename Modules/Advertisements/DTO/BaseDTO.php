<?php

namespace Modules\Advertisements\DTO;

use Illuminate\Validation\ValidationException;

/**
 * Base Data Transfer Object
 */
abstract class BaseDTO
{
    /**
     * Get all DTO properties as array
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $data = [];

        foreach ($properties as $property) {
            $data[$property->getName()] = $this->{$property->getName()} ?? null;
        }

        return array_filter($data, fn($value) => !is_null($value));
    }

    /**
     * Create DTO from array
     */
    public static function fromArray(array $data): self
    {
        $dto = new static();
        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->{$key} = $value;
            }
        }
        return $dto;
    }
}
