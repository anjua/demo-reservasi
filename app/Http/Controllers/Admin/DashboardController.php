<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\CarbonImmutable;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today = CarbonImmutable::today();
        $monthStart = $today->startOfMonth();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'today' => Reservation::active()->whereDate('date', $today)->count(),
                'pending' => Reservation::where('status', 'pending')->whereDate('date', '>=', $today)->count(),
                'month' => Reservation::active()->whereBetween('date', [$monthStart, $today->endOfMonth()])->count(),
                'revenue' => (int) Reservation::where('status', 'completed')
                    ->whereBetween('date', [$monthStart, $today->endOfMonth()])->sum('price'),
            ],
            'todayList' => Reservation::with('service:id,name')
                ->whereDate('date', $today)
                ->where('status', '!=', 'cancelled')
                ->orderBy('start_time')->get(),
            'pendingList' => Reservation::with('service:id,name')
                ->where('status', 'pending')
                ->whereDate('date', '>=', $today)
                ->orderBy('date')->orderBy('start_time')
                ->limit(8)->get(),
        ]);
    }
}
