<?php

namespace App\Http\Livewire\Crm;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Target;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public string $tab = 'overview';

    public string $period = 'monthly';

    public string $analyticsDate = '';

    public string $staffTimeframe = 'monthly';

    public string $staffDate = '';

    public ?int $selectedStaffId = null;

    protected $layout = 'layouts.crm';

    public function mount(): void
    {
        $this->analyticsDate = now()->toDateString();
        $this->staffDate = now()->toDateString();
    }

    /**
     * The Overview tab already shows the big Fava panel — tell the site-wide
     * floating widget to hide itself while that tab is active, since Livewire
     * swaps tabs without a page reload and the widget lives outside this
     * component's own re-rendered markup.
     */
    public function updatedTab(): void
    {
        $this->dispatchBrowserEvent('dashboard-tab-changed', ['tab' => $this->tab]);
    }

    protected function periodRange(string $period, string $anchor): array
    {
        $date = Carbon::parse($anchor);

        return match ($period) {
            'daily' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'weekly' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'yearly' => [$date->copy()->startOfYear(), $date->copy()->endOfYear()],
            default => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
        };
    }

    public function getOverviewProperty(): array
    {
        return [
            'total_revenue' => Invoice::sum('grand_total'),
            'customers' => Customer::count(),
            'hot_deals' => Deal::where('stage', Deal::STAGE_HOT)->count(),
            'total_queries' => \App\Models\SalesQuery::count(),
            'recent_sales' => Invoice::with('customer')->latest()->limit(5)->get(),
            'recent_customers' => Customer::latest()->limit(5)->get(),
            'customers_this_month' => Customer::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }

    public function getAnalyticsProperty(): array
    {
        [$start, $end] = $this->periodRange($this->period, $this->analyticsDate);
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
            ->where('period_start', '<=', $this->analyticsDate)
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

    /** Total Sales for the last 6 periods (same granularity as the Analytics filter) — feeds the trend chart. */
    public function getSalesTrendProperty(): array
    {
        $points = [];

        for ($i = 5; $i >= 0; $i--) {
            $anchor = match ($this->period) {
                'daily' => Carbon::parse($this->analyticsDate)->subDays($i),
                'weekly' => Carbon::parse($this->analyticsDate)->subWeeks($i),
                'yearly' => Carbon::parse($this->analyticsDate)->subYears($i),
                default => Carbon::parse($this->analyticsDate)->subMonths($i),
            };

            [$start, $end] = $this->periodRange($this->period, $anchor->toDateString());

            $label = match ($this->period) {
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

    public function getStaffListProperty()
    {
        [$start, $end] = $this->periodRange($this->staffTimeframe, $this->staffDate);

        return User::crmStaff()->get()->map(function (User $user) use ($start, $end) {
            $user->period_sales = Invoice::where('staff_id', $user->id)->whereBetween('invoice_date', [$start, $end])->sum('grand_total');

            return $user;
        })->sortByDesc('period_sales')->values();
    }

    public function getSelectedStaffProperty(): ?User
    {
        $id = $this->selectedStaffId ?? $this->staffList->first()?->id;

        if (! $id) {
            return null;
        }

        [$start, $end] = $this->periodRange($this->staffTimeframe, $this->staffDate);
        $user = User::find($id);

        if (! $user) {
            return null;
        }

        $user->kpi_sales = Invoice::where('staff_id', $user->id)->whereBetween('invoice_date', [$start, $end])->sum('grand_total');
        $user->kpi_leads = Customer::where('added_by', $user->id)->whereBetween('created_at', [$start, $end])->count();
        $user->kpi_quotations = Quotation::where('staff_id', $user->id)->whereBetween('created_at', [$start, $end])->count();
        $user->kpi_calls = Activity::where('done_by', $user->id)->where('type', 'Call')->whereBetween('date', [$start, $end])->count();
        $user->kpi_potential_deals = Deal::where('assigned_staff_id', $user->id)->where('stage', Deal::STAGE_POTENTIAL)
            ->whereBetween('created_at', [$start, $end])->count();

        return $user;
    }

    public function render()
    {
        return view('crm.dashboard.index')->layout('layouts.crm');
    }
}
