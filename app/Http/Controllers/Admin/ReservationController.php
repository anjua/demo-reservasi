<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only('status', 'date', 'search', 'service_id');

        $reservations = Reservation::with('service:id,name')
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['date'] ?? null, fn ($q, $v) => $q->whereDate('date', $v))
            ->when($filters['service_id'] ?? null, fn ($q, $v) => $q->where('service_id', $v))
            ->when($filters['search'] ?? null, function ($q, $v) {
                $q->where(fn ($q) => $q->where('customer_name', 'like', "%{$v}%")
                    ->orWhere('customer_phone', 'like', "%{$v}%")
                    ->orWhere('code', 'like', "%{$v}%"));
            })
            ->orderByDesc('date')->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Reservations/Index', [
            'reservations' => $reservations,
            'filters' => $filters,
            'statuses' => Reservation::STATUSES,
            'services' => Service::ordered()->get(['id', 'name']),
        ]);
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Reservation::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $reservation->update($data);

        return back()->with('success', "Reservasi {$reservation->code} diubah menjadi \"{$reservation->status_label}\".");
    }
}
