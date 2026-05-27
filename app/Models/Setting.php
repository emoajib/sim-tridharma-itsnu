<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key', 'value', 'type', 'description',
    ];

    protected function casts(): array
    {
        return [];
    }

    public static function getAllCached(): array
    {
        return Cache::remember('app_settings', 3600, function () {
            return static::all()->keyBy('key')->mapWithKeys(function ($setting) {
                $value = match ($setting->type) {
                    'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                    'number' => is_numeric($setting->value) ? (float) $setting->value : $setting->value,
                    'json' => json_decode($setting->value, true),
                    default => $setting->value,
                };
                return [$setting->key => $value];
            })->toArray();
        });
    }

    public static function get($key, $default = null)
    {
        $settings = static::getAllCached();

        if (!array_key_exists($key, $settings)) {
            return $default;
        }

        return $settings[$key];
    }

    public static function set($key, $value, $type = 'string', $description = null): void
    {
        $value = match ($type) {
            'json' => is_array($value) ? json_encode($value) : $value,
            'boolean' => $value ? 'true' : 'false',
            'number' => (string) $value,
            default => $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );

        Cache::forget('app_settings');
    }
}
