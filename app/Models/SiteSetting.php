<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'float' => (float) $setting->value,
            default => $setting->value,
        };
    }

    public static function setValue(string $key, mixed $value, ?string $description = null, ?string $type = null): self
    {
        $setting = static::where('key', $key)->firstOrNew();
        $setting->key = $key;
        $setting->value = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
        $setting->type = $type ?? 'string';
        $setting->description = $description;
        $setting->save();

        return $setting;
    }
}
