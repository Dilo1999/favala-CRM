<?php

namespace App\Http\Livewire\Crm;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\User;
use App\Services\DashboardMetricsService;
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
        return (new DashboardMetricsService)->periodRange($period, $anchor);
    }

    public function getOverviewProperty(): array
    {
        return (new DashboardMetricsService)->overview();
    }

    public function getAnalyticsProperty(): array
    {
        return (new DashboardMetricsService)->analytics($this->period, $this->analyticsDate);
    }

    /** Total Sales for the last 6 periods (same granularity as the Analytics filter) — feeds the trend chart. */
    public function getSalesTrendProperty(): array
    {
        return (new DashboardMetricsService)->salesTrend($this->period, $this->analyticsDate);
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
