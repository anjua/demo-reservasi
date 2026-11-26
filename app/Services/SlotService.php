<?php

namespace App\Services;

use App\Models\BusinessHour;
use App\Models\ClosedDate;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * Menghitung slot jam yang tersedia untuk sebuah layanan pada tanggal tertentu.
 *
 * Aturan:
 * - Tanggal harus hari buka (jam operasional) dan bukan tanggal libur.
 * - Slot dibuat tiap `slot_interval` menit, dan layanan harus selesai sebelum jam tutup.
 * - Jumlah reservasi aktif yang bertumpang-tindih dengan slot harus < `capacity_per_slot`.
 * - Slot yang kurang dari `min_notice_minutes` dari sekarang tidak ditampilkan.
 */
class SlotService
{
    public function closedReason(CarbonImmutable $date): ?string
    {
        $today = CarbonImmutable::today();
        $maxDays = (int) Setting::get('max_days_ahead');

        if ($date->lt($today)) {
            return 'Tanggal sudah lewat.';
        }
        if ($date->gt($today->addDays($maxDays))) {
            return "Booking hanya bisa maksimal {$maxDays} hari ke depan.";
        }

        $closed = ClosedDate::whereDate('date', $date->toDateString())->first();
        if ($closed) {
            return 'Tutup: '.($closed->reason ?: 'libur');
        }

        $hours = BusinessHour::where('day_of_week', $date->dayOfWeek)->first();
        if (! $hours || ! $hours->is_open) {
            return 'Tutup setiap hari '.BusinessHour::DAY_NAMES[$date->dayOfWeek].'.';
        }

        return null;
    }

    /**
     * @return array<int, array{time: string, end: string, available: bool, remaining: int}>
     */
    public function slotsFor(Service $service, CarbonImmutable $date, ?int $ignoreReservationId = null): array
    {
        if ($this->closedReason($date)) {
            return [];
        }

        $hours = BusinessHour::where('day_of_week', $date->dayOfWeek)->first();
        $interval = max(5, (int) Setting::get('slot_interval'));
        $capacity = max(1, (int) Setting::get('capacity_per_slot'));
        $earliest = Carbon::now()->addMinutes((int) Setting::get('min_notice_minutes'));

        $open = $date->setTimeFromTimeString($hours->open_time);
        $close = $date->setTimeFromTimeString($hours->close_time);

        $existing = Reservation::active()
            ->whereDate('date', $date->toDateString())
            ->when($ignoreReservationId, fn ($q) => $q->whereKeyNot($ignoreReservationId))
            ->get(['start_time', 'end_time']);

        $slots = [];
        for ($start = $open; $start->addMinutes($service->duration_minutes)->lte($close); $start = $start->addMinutes($interval)) {
            $end = $start->addMinutes($service->duration_minutes);
            $startStr = $start->format('H:i');
            $endStr = $end->format('H:i');

            if ($start->lt($earliest)) {
                continue; // jam sudah lewat / terlalu mepet, tidak perlu ditampilkan
            }

            $overlapping = $existing->filter(
                fn ($r) => $r->start_time < $endStr && $r->end_time > $startStr
            )->count();

            $remaining = max(0, $capacity - $overlapping);
            $slots[] = [
                'time' => $startStr,
                'end' => $endStr,
                'available' => $remaining > 0,
                'remaining' => $remaining,
            ];
        }

        return $slots;
    }

    public function isAvailable(Service $service, CarbonImmutable $date, string $time, ?int $ignoreReservationId = null): bool
    {
        foreach ($this->slotsFor($service, $date, $ignoreReservationId) as $slot) {
            if ($slot['time'] === $time) {
                return $slot['available'];
            }
        }

        return false;
    }
}
