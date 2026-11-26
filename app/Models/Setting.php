<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Nilai bawaan bila belum diatur di dashboard. */
    public const DEFAULTS = [
        'slot_interval' => 30,      // jarak antar slot (menit)
        'capacity_per_slot' => 2,   // jumlah pelanggan yang bisa dilayani bersamaan (mis. jumlah staf/kursi)
        'min_notice_minutes' => 60, // minimal booking sekian menit sebelum jam mulai
        'max_days_ahead' => 30,     // pelanggan bisa booking sampai sekian hari ke depan
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('settings', fn () => static::query()->pluck('value', 'key')->all());

        return $all[$key] ?? $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public static function put(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings');
    }

    public static function allWithDefaults(): array
    {
        $values = [];
        foreach (self::DEFAULTS as $key => $default) {
            $values[$key] = (int) static::get($key, $default);
        }

        return $values;
    }
}
