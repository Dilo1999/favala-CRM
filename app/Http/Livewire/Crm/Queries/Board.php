<?php

namespace App\Http\Livewire\Crm\Queries;

use App\Models\Customer;
use App\Models\SalesQuery;
use App\Models\SettingOption;
use App\Models\User;
use Livewire\Component;

class Board extends Component
{
    public string $search = '';

    public string $timeFilter = 'all';

    public bool $showNewQueryForm = false;

    public array $newQuery = [
        'query_source' => null, 'assigned_staff_id' => null,
        'customer_name' => '', 'customer_phone' => '',
        'query_type' => null, 'product_category' => null,
        'description' => '',
    ];

    protected function baseQuery()
    {
        $query = SalesQuery::query()->with(['customer', 'assignedStaff']);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->whereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%"))
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        match ($this->timeFilter) {
            'today' => $query->whereDate('created_at', now()->toDateString()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            default => null,
        };

        return $query;
    }

    public function getColumnsProperty(): array
    {
        $all = $this->baseQuery()->latest()->get();

        return [
            SalesQuery::STATUS_NEW => $all->where('status', SalesQuery::STATUS_NEW)->values(),
            SalesQuery::STATUS_NEGOTIATING => $all->where('status', SalesQuery::STATUS_NEGOTIATING)->values(),
            SalesQuery::STATUS_COMPLETED => $all->where('status', SalesQuery::STATUS_COMPLETED)->values(),
            SalesQuery::STATUS_DEAD => $all->where('status', SalesQuery::STATUS_DEAD)->values(),
        ];
    }

    public function getKpisProperty(): array
    {
        $counts = $this->baseQuery()->get()->countBy('status');

        return [
            'total' => $counts->sum(),
            'new' => $counts->get(SalesQuery::STATUS_NEW, 0),
            'negotiating' => $counts->get(SalesQuery::STATUS_NEGOTIATING, 0),
            'completed' => $counts->get(SalesQuery::STATUS_COMPLETED, 0),
        ];
    }

    public function getStaffProperty()
    {
        return User::crmStaff()->orderBy('name')->pluck('name', 'id');
    }

    public function getQuerySourcesProperty(): array
    {
        return SettingOption::options(SettingOption::QUERY_SOURCE);
    }

    public function getQueryTypesProperty(): array
    {
        return SettingOption::options(SettingOption::QUERY_TYPE);
    }

    public function getProductCategoriesProperty(): array
    {
        return SettingOption::options(SettingOption::PRODUCT_CATEGORY);
    }

    public function moveTo(int $queryId, string $status): void
    {
        SalesQuery::whereKey($queryId)->update(['status' => $status]);
    }

    public function delete(int $queryId): void
    {
        SalesQuery::whereKey($queryId)->delete();
        session()->flash('status', 'Query deleted.');
    }

    public function closeNewQueryForm(): void
    {
        $this->showNewQueryForm = false;
        $this->resetNewQueryForm();
    }

    public function createQuery(): void
    {
        $this->validate([
            'newQuery.customer_name' => 'required|string|max:191',
            'newQuery.customer_phone' => 'nullable|string|max:60',
            'newQuery.query_source' => 'nullable|string',
            'newQuery.query_type' => 'nullable|string',
            'newQuery.product_category' => 'nullable|string',
            'newQuery.assigned_staff_id' => 'nullable|exists:users,id',
            'newQuery.description' => 'nullable|string',
        ]);

        // De-dupe by phone when given, so logging the same caller twice doesn't fork their record.
        $customer = $this->newQuery['customer_phone']
            ? Customer::firstOrNew(['phone' => $this->newQuery['customer_phone']])
            : new Customer();

        if (! $customer->exists) {
            $customer->fill([
                'company_name' => $this->newQuery['customer_name'],
                'phone' => $this->newQuery['customer_phone'] ?: null,
                'lead_source' => $this->newQuery['query_source'],
                'status' => Customer::STATUS_NEW,
                'added_by' => auth()->id(),
            ])->save();
        }

        SalesQuery::create([
            'customer_id' => $customer->id,
            'phone' => $this->newQuery['customer_phone'] ?: $customer->phone,
            'description' => $this->newQuery['description'],
            'tags' => array_values(array_filter([$this->newQuery['product_category']])),
            'source' => 'manual',
            'status' => SalesQuery::STATUS_NEW,
            'query_source' => $this->newQuery['query_source'],
            'query_type' => $this->newQuery['query_type'],
            'assigned_staff_id' => $this->newQuery['assigned_staff_id'],
        ]);

        $this->closeNewQueryForm();
        session()->flash('status', 'Query logged.');
    }

    protected function resetNewQueryForm(): void
    {
        $this->newQuery = [
            'query_source' => null, 'assigned_staff_id' => null,
            'customer_name' => '', 'customer_phone' => '',
            'query_type' => null, 'product_category' => null,
            'description' => '',
        ];
    }

    public function render()
    {
        return view('crm.queries.board')->layout('layouts.crm');
    }
}
