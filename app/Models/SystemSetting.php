<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $table = 'system_settings';
    public $timestamps = false;

    protected $fillable = [
        'category',
        'name',
        'value',
        'type',
        'description',
    ];

    public static function getValue(string $category, string $name, mixed $default = null): mixed
    {
        $cacheKey = "setting.{$category}.{$name}";

        return Cache::remember($cacheKey, 300, function () use ($category, $name, $default) {
            $setting = static::where('category', $category)
                ->where('name', $name)
                ->first();

            if (!$setting) {
                return $default;
            }

            return match($setting->type) {
                'int' => (int) $setting->value,
                'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function setValue(string $category, string $name, mixed $value): void
    {
        $stringValue = is_array($value) ? json_encode($value) : (string) $value;

        static::updateOrCreate(
            ['category' => $category, 'name' => $name],
            ['value' => $stringValue]
        );

        Cache::forget("setting.{$category}.{$name}");
    }

    public static function getByCategory(string $category): array
    {
        return static::where('category', $category)
            ->pluck('value', 'name')
            ->toArray();
    }
}
