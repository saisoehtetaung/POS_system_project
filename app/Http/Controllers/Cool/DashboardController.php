<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Analytics\Services\DashboardAnalyticsService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardAnalyticsService $analytics)
    {
        $shopId = auth()->user()->shop_id;
        $canViewCost = auth()->user()->canViewCost();

        [$from, $to, $preset] = $this->resolveRange($request);

        $payload = $analytics->build($shopId, $from, $to, $canViewCost);

        return view('cool.dashboard', [
            'kpis' => $payload['kpis'],
            'charts' => $payload['charts'],
            'tables' => $payload['tables'],
            'range' => [
                'preset' => $preset,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'canViewCost' => $canViewCost,
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveRange(Request $request): array
    {
        $preset = (string) $request->input('preset', '30d');
        $allowed = ['today', '7d', '30d', 'month', 'custom'];
        if (! in_array($preset, $allowed, true)) {
            $preset = '30d';
        }

        $today = today();

        return match ($preset) {
            'today' => [$today->copy()->startOfDay(), $today->copy()->endOfDay(), 'today'],
            '7d' => [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay(), '7d'],
            'month' => [$today->copy()->startOfMonth()->startOfDay(), $today->copy()->endOfDay(), 'month'],
            'custom' => $this->resolveCustomRange($request),
            default => [$today->copy()->subDays(29)->startOfDay(), $today->copy()->endOfDay(), '30d'],
        };
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveCustomRange(Request $request): array
    {
        $fromInput = $request->input('from');
        $toInput = $request->input('to');

        try {
            $from = $fromInput ? Carbon::parse($fromInput)->startOfDay() : today()->subDays(29)->startOfDay();
        } catch (\Throwable) {
            $from = today()->subDays(29)->startOfDay();
        }

        try {
            $to = $toInput ? Carbon::parse($toInput)->endOfDay() : today()->endOfDay();
        } catch (\Throwable) {
            $to = today()->endOfDay();
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        // Cap custom range to 90 days for chart readability / query cost
        if ($from->diffInDays($to) > 90) {
            $from = $to->copy()->subDays(90)->startOfDay();
        }

        return [$from, $to, 'custom'];
    }
}
