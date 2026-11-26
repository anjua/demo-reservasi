<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    public const STATUSES = [
        'pending' => 'Menunggu Konfirmasi',
        'confirmed' => 'Dikonfirmasi',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    /** Status yang masih "memakan" slot. */
    public const ACTIVE_STATUSES = ['pending', 'confirmed', 'completed'];

    protected $fillable = [
        'code', 'service_id', 'customer_name', 'customer_phone', 'customer_email', 'notes',
        'date', 'start_time', 'end_time', 'price', 'status', 'admin_note',
    ];

    protected $appends = ['status_label'];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'price' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Reservation $reservation) {
            if (! $reservation->code) {
                do {
                    $code = 'RSV-'.strtoupper(Str::random(6));
                } while (static::where('code', $code)->exists());
                $reservation->code = $code;
            }
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStartTimeAttribute($value): string
    {
        return substr((string) $value, 0, 5);
    }

    public function getEndTimeAttribute($value): string
    {
        return substr((string) $value, 0, 5);
    }
}
