<?php

namespace App\Http\Livewire\Crm\Targets;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Target;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Index extends Component
{
    public string $period = 'monthly';

    public string $anchorDate;

    public bool $showCompanyForm = false;

    public bool $showStaffForm = false;

    public ?int $staffFormUserId = null;

    public array $companyForm = ['sales' => 0, 'quotations' => 0, 'deals' => 0, 'meetings' => 0, 'calls' => 0, 'site_visits' => 0, 'new_leads' => 0];

    public array $staffForm = ['sales' => 0, 'quotations' => 0, 'deals' => 0, 'meetings' => 0, 'calls' => 0, 'site_visits' => 0, 'new_leads' => 0];

    public function mount(): void
    {
        $this->anchorDate = now()->toDateString();
    }

    protected function periodStart(): Carbon
    {
        $date = Carbon::parse($this->anchorDate);

        return match ($this->period) {
            'daily' => $date->copy()->startOfDay(),
            'weekly' => $date->copy()->startOfWeek(),
            'yearly' => $date->copy()->startOfYear(),
            default => $date->copy()->startOfMonth(),
        };
    }

    protected function periodEnd(): Carbon
    {
        $start = $this->periodStart();

        return match ($this->period) {
            'daily' => $start->copy()->endOfDay(),
            'weekly' => $start->copy()->endOfWeek(),
            'yearly' => $start->copy()->endOfYear(),
            default => $start->copy()->endOfMonth(),
        };
    }

    public function getPeriodLabelProperty(): string
    {
        return match ($this->period) {
            'daily' => $this->periodStart()->format('d M Y'),
            'weekly' => 'Week of '.$this->periodStart()->format('d M Y'),
            'yearly' => $this->periodStart()->format('Y'),
            default => $this->periodStart()->format('F Y'),
        };
    }

    protected function achieved(string $metric, ?int $userId = null): float
    {
        [$start, $end] = [$this->periodStart(), $this->periodEnd()];

        return match ($metric) {
            'sales' => (float) Invoice::when($userId, fn ($q) => $q->where('staff_id', $userId))
                ->whereBetween('invoice_date', [$start, $end])->sum('grand_total'),
            'quotations' => Quotation::when($userId, fn ($q) => $q->where('staff_id', $userId))
                ->whereBetween('created_at', [$start, $end])->count(),
            'deals' => Deal::when($userId, fn ($q) => $q->where('assigned_staff_id', $userId))
                ->whereBetween('created_at', [$start, $end])->count(),
            'meetings' => Task::where('type', 'Meeting')->when($userId, fn ($q) => $q->where('assigned_to', $userId))
                ->whereBetween('created_at', [$start, $end])->count(),
            'calls' => Activity::where('type', 'Call')->when($userId, fn ($q) => $q->where('done_by', $userId))
                ->whereBetween('date', [$start, $end])->count(),
            'site_visits' => Task::where('type', 'Site Visit')->when($userId, fn ($q) => $q->where('assigned_to', $userId))
                ->whereBetween('created_at', [$start, $end])->count(),
            'new_leads' => Customer::when($userId, fn ($q) => $q->where('added_by', $userId))
                ->whereBetween('created_at', [$start, $end])->count(),
            default => 0,
        };
    }

    public function getCompanyTargetProperty(): ?Target
    {
        return Target::where('scope', 'company')->where('period', $this->period)
            ->where('period_start', $this->periodStart()->toDateString())->first();
    }

    public function getCompanyAchievedProperty(): array
    {
        return collect(['sales', 'quotations', 'deals', 'meetings', 'calls', 'site_visits', 'new_leads'])
            ->mapWithKeys(fn ($m) => [$m => $this->achieved($m)])->all();
    }

    public function getStaffRowsProperty()
    {
        return User::crmStaff()->get()->map(function (User $user) {
            $target = Target::where('scope', 'staff')->where('user_id', $user->id)
                ->where('period', $this->period)->where('period_start', $this->periodStart()->toDateString())->first();

            $user->target = $target;
            $user->achieved = collect(['sales', 'calls', 'meetings', 'new_leads', 'site_visits'])
                ->mapWithKeys(fn ($m) => [$m => $this->achieved($m, $user->id)]);

            return $user;
        });
    }

    public function openCompanyForm(): void
    {
        $target = $this->companyTarget;
        $this->companyForm = $target ? $target->only(array_keys($this->companyForm)) : $this->companyForm;
        $this->showCompanyForm = true;
    }

    public function saveCompanyTarget(): void
    {
        Target::updateOrCreate(
            ['scope' => 'company', 'user_id' => null, 'period' => $this->period, 'period_start' => $this->periodStart()->toDateString()],
            $this->companyForm
        );
        $this->showCompanyForm = false;
        session()->flash('status', 'Company targets updated.');
    }

    public function openStaffForm(int $userId): void
    {
        $this->staffFormUserId = $userId;
        $target = Target::where('scope', 'staff')->where('user_id', $userId)
            ->where('period', $this->period)->where('period_start', $this->periodStart()->toDateString())->first();
        $this->staffForm = $target ? $target->only(array_keys($this->staffForm)) : $this->staffForm;
        $this->showStaffForm = true;
    }

    public function saveStaffTarget(): void
    {
        Target::updateOrCreate(
            ['scope' => 'staff', 'user_id' => $this->staffFormUserId, 'period' => $this->period, 'period_start' => $this->periodStart()->toDateString()],
            $this->staffForm
        );
        $this->showStaffForm = false;
        session()->flash('status', 'Staff targets updated.');
    }

    public function render()
    {
        return view('crm.targets.index')->layout('layouts.crm');
    }
}
