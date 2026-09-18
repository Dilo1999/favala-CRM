<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Target;
use Illuminate\Support\Carbon;

/**
 * Company-wide dashboard aggregates — shared by the Dashboard Livewire
 * component and the Fava assistant's get_business_overview tool, so the
 * numbers the bot reports can never drift from what the dashboard shows.
 */
class DashboardMetricsService
{
    public function periodRange(string $period, string $anchor): array
    {
        $date = Carbon::parse($anchor);

        return match ($period) {
            'daily' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'weekly' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'yearly' => [$date->copy()->startOfYear(), $date->copy()->endOfYear()],
            default => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
        };
    }

    public function overview(): array
    {
        return [
            'total_revenue' => Invoice::sum('grand_total'),
            'customers' => Customer::count(),
            'hot_deals' => Deal::where('stage', Deal::STAGE_HOT)->count(),
            'total_queries' => \App\Models\SalesQuery::count(),
            'recent_sales' => Invoice::with(['customer', 'returns.items'])->latest()->limit(5)->get(),
            'recent_customers' => Customer::latest()->limit(5)->get(),
            'customers_this_month' => Customer::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }

    public function analytics(string $period, string $anchorDate): array
    {
        [$start, $end] = $this->periodRange($period, $anchorDate);
        $length = $start->diffInSeconds($end);
        $prevStart = (clone $start)->subSeconds($length + 1);
        $prevEnd = (clone $start)->subSecond();

        $metric = function (callable $current, callable $previous) use ($start, $end, $prevStart, $prevEnd) {
            $currentValue = $current($start, $end);
            $previousValue = $previous($prevStart, $prevEnd);
            $change = $previousValue > 0 ? round((($currentValue - $previousValue) / $previousValue) * 100, 1) : ($currentValue > 0 ? 100.0 : 0.0);

            return ['value' => $currentValue, 'change' => $change];
        };

        $target = Target::where('scope', 'company')
            ->where('period_start', '<=', $anchorDate)
            ->orderByDesc('period_start')->first();

        $totalSales = $metric(
            fn ($s, $e) => (float) Invoice::whereBetween('invoice_date', [$s, $e])->sum('grand_total'),
            fn ($s, $e) => (float) Invoice::whereBetween('invoice_date', [$s, $e])->sum('grand_total'),
        );

        return [
            'target' => $target,
            'rows' => [
                'Total Sales' => $totalSales,
                'No. of Invoices' => $metric(
                    fn ($s, $e) => Invoice::whereBetween('invoice_date', [$s, $e])->count(),
                    fn ($s, $e) => Invoice::whereBetween('invoice_date', [$s, $e])->count(),
                ),
                'Achieved %' => ['value' => $target && $target->sales > 0 ? round(($totalSales['value'] / $target->sales) * 100, 1) : 0, 'change' => 0],
                'No. of Deals' => $metric(
                    fn ($s, $e) => Deal::whereBetween('created_at', [$s, $e])->count(),
                    fn ($s, $e) => Deal::whereBetween('created_at', [$s, $e])->count(),
                ),
                'No. of Quotations' => $metric(
                    fn ($s, $e) => Quotation::whereBetween('created_at', [$s, $e])->count(),
                    fn ($s, $e) => Quotation::whereBetween('created_at', [$s, $e])->count(),
                ),
                'Value of Quotations' => $metric(
                    fn ($s, $e) => (float) Quotation::whereBetween('created_at', [$s, $e])->sum('grand_total'),
                    fn ($s, $e) => (float) Quotation::whereBetween('created_at', [$s, $e])->sum('grand_total'),
                ),
                'No. of Potential Deals' => $metric(
                    fn ($s, $e) => Deal::where('stage', Deal::STAGE_POTENTIAL)->whereBetween('created_at', [$s, $e])->count(),
                    fn ($s, $e) => Deal::where('stage', Deal::STAGE_POTENTIAL)->whereBetween('created_at', [$s, $e])->count(),
                ),
                'No. of Calls Made' => $metric(
                    fn ($s, $e) => Activity::where('type', 'Call')->whereBetween('date', [$s, $e])->count(),
                    fn ($s, $e) => Activity::where('type', 'Call')->whereBetween('date', [$s, $e])->count(),
                ),
                'No. Invoiced' => $metric(
                    fn ($s, $e) => Invoice::whereBetween('invoice_date', [$s, $e])->where('payment_status', 'paid')->count(),
                    fn ($s, $e) => Invoice::whereBetween('invoice_date', [$s, $e])->where('payment_status', 'paid')->count(),
                ),
                'Lost Deals' => $metric(
                    fn ($s, $e) => Deal::where('stage', Deal::STAGE_LOST)->whereBetween('created_at', [$s, $e])->count(),
                    fn ($s, $e) => Deal::where('stage', Deal::STAGE_LOST)->whereBetween('created_at', [$s, $e])->count(),
                ),
            ],
        ];
    }

    /** Total Sales for the last 6 periods (same granularity as the Analytics filter). */
    public function salesTrend(string $period, string $anchorDate): array
    {
        $points = [];

        for ($i = 5; $i >= 0; $i--) {
            $anchor = match ($period) {
                'daily' => Carbon::parse($anchorDate)->subDays($i),
                'weekly' => Carbon::parse($anchorDate)->subWeeks($i),
                'yearly' => Carbon::parse($anchorDate)->subYears($i),
                default => Carbon::parse($anchorDate)->subMonths($i),
            };

            [$start, $end] = $this->periodRange($period, $anchor->toDateString());

            $label = match ($period) {
                'daily', 'weekly' => $start->format('d M'),
                'yearly' => $start->format('Y'),
                default => $start->format('M Y'),
            };

            $points[] = [
                'label' => $label,
                'value' => (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('grand_total'),
            ];
        }

        return $points;
    }
}
