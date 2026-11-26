<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    public const DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    protected $fillable = ['day_of_week', 'is_open', 'open_time', 'close_time'];

    protected $appends = ['day_name'];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'day_of_week' => 'integer',
        ];
    }

    public function getDayNameAttribute(): string
    {
        return self::DAY_NAMES[$this->day_of_week] ?? '-';
    }

    public function getOpenTimeAttribute($value): string
    {
        return substr((string) $value, 0, 5);
    }

    public function getCloseTimeAttribute($value): string
    {
        return substr((string) $value, 0, 5);
    }
}
