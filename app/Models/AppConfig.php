<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    /**
     * Return the value cast to its declared type.
     */
    public function getCastValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            default   => $this->value,
        };
    }

    /**
     * Convenience: get a config value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $record = static::where('key', $key)->first();
        return $record ? $record->cast_value : $default;
    }
}
