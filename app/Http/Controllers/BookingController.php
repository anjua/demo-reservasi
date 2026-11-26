<?php

namespace App\Http\Controllers;

use App\Models\BusinessHour;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Setting;
use App\Services\SlotService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(private SlotService $slots) {}

    /** Halaman utama: pilih layanan, tanggal, jam, isi data. */
    public function index(): Response
    {
        $today = CarbonImmutable::today();
        $maxDays = (int) Setting::get('max_days_ahead');

        $days = [];
        for ($i = 0; $i <= $maxDays; $i++) {
            $date = $today->addDays($i);
            $reason = $this->slots->closedReason($date);
            $days[] = [
                'date' => $date->toDateString(),
                'closed' => (bool) $reason,
                'reason' => $reason,
            ];
        }

        return Inertia::render('Booking/Index', [
            'services' => Service::active()->ordered()->get(),
            'days' => $days,
            'hours' => BusinessHour::orderBy('day_of_week')->get(),
        ]);
    }

    /** Slot jam yang tersedia (dipanggil via fetch dari halaman booking). */
    public function slots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $service = Service::active()->findOrFail($data['service_id']);
        $date = CarbonImmutable::parse($data['date']);

        return response()->json([
            'closed_reason' => $this->slots->closedReason($date),
            'slots' => $this->slots->slotsFor($service, $date),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+?62|0)8[0-9]{7,12}$/'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'customer_phone.regex' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890.',
        ], [
            'service_id' => 'layanan',
            'date' => 'tanggal',
            'time' => 'jam',
            'customer_name' => 'nama',
            'customer_phone' => 'nomor WhatsApp',
            'customer_email' => 'email',
            'notes' => 'catatan',
        ]);

        $service = Service::active()->findOrFail($data['service_id']);
        $date = CarbonImmutable::parse($data['date']);

        $reservation = DB::transaction(function () use ($service, $date, $data) {
            // Cek ulang ketersediaan di dalam transaksi agar tidak terjadi double booking.
            if (! $this->slots->isAvailable($service, $date, $data['time'])) {
                throw ValidationException::withMessages([
                    'time' => 'Maaf, jam tersebut baru saja penuh. Silakan pilih jam lain.',
                ]);
            }

            $start = $date->setTimeFromTimeString($data['time']);

            return Reservation::create([
                'service_id' => $service->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $this->normalizePhone($data['customer_phone']),
                'customer_email' => $data['customer_email'] ?? null,
                'notes' => $data['notes'] ?? null,
                'date' => $date->toDateString(),
                'start_time' => $start->format('H:i'),
                'end_time' => $start->addMinutes($service->duration_minutes)->format('H:i'),
                'price' => $service->price,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('booking.show', $reservation->code);
    }

    /** Halaman bukti booking. Bisa dibuka ulang dengan kode reservasi. */
    public function show(string $code): Response
    {
        $reservation = Reservation::with('service')->where('code', $code)->firstOrFail();

        return Inertia::render('Booking/Success', [
            'reservation' => $reservation,
        ]);
    }

    public function check(Request $request): Response
    {
        $result = null;
        $searched = false;

        if ($request->filled('code') || $request->filled('phone')) {
            $searched = true;
            $request->validate([
                'code' => ['required', 'string'],
                'phone' => ['required', 'string'],
            ], [], ['code' => 'kode booking', 'phone' => 'nomor WhatsApp']);

            $result = Reservation::with('service')
                ->where('code', strtoupper(trim($request->code)))
                ->where('customer_phone', $this->normalizePhone($request->phone))
                ->first();
        }

        return Inertia::render('Booking/Check', [
            'filters' => $request->only('code', 'phone'),
            'result' => $result,
            'searched' => $searched,
        ]);
    }

    /** Ubah 0812... / +62812... menjadi 62812... */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }
}
