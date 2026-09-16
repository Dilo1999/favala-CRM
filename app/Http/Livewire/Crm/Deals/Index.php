<?php

namespace App\Http\Livewire\Crm\Deals;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\SettingOption;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public string $tab = 'all';

    public string $viewMode = 'list';

    public ?int $viewingId = null;

    public ?int $editingId = null;

    public array $editForm = [
        'customer_id' => null, 'deal_date' => '', 'request_source' => null,
        'assigned_staff_id' => null, 'stage' => 'potential', 'additional_details' => null,
    ];

    protected function baseQuery()
    {
        return Deal::with(['customer', 'assignedStaff'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->whereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%"))
                    ->orWhereHas('assignedStaff', fn ($s) => $s->where('name', 'like', "%{$this->search}%"))
                    ->orWhere('id', 'like', "%{$this->search}%");
            }));
    }

    public function getKpisProperty(): array
    {
        return [
            'in_progress' => Deal::whereIn('stage', ['potential', 'hot'])->whereNull('converted_at')
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))->count(),
            'potential' => Deal::where('stage', 'potential')->count(),
            'hot' => Deal::where('stage', 'hot')->count(),
            'mine' => Deal::where('assigned_staff_id', auth()->id())->count(),
            'converted' => Deal::where('stage', 'won')->count(),
        ];
    }

    public function getViewingDealProperty(): ?Deal
    {
        return $this->viewingId
            ? Deal::with(['customer', 'assignedStaff', 'createdBy', 'products.product', 'quotations'])->find($this->viewingId)
            : null;
    }

    public function view(int $id): void
    {
        $this->editingId = null;
        $this->viewingId = $id;
    }

    public function closeView(): void
    {
        $this->viewingId = null;
    }

    public function edit(int $id): void
    {
        $deal = Deal::findOrFail($id);
        $this->viewingId = null;
        $this->editingId = $id;
        $this->editForm = [
            'customer_id' => $deal->customer_id,
            'deal_date' => $deal->deal_date->toDateString(),
            'request_source' => $deal->request_source,
            'assigned_staff_id' => $deal->assigned_staff_id,
            'stage' => $deal->stage,
            'additional_details' => $deal->additional_details,
        ];
    }

    public function closeEdit(): void
    {
        $this->editingId = null;
    }

    protected function editRules(): array
    {
        // "won" is reached only through Quotation::convertToInvoice() ->
        // markWon(), which also stamps converted_at — not a value this form
        // should ever be able to submit for a deal that isn't won yet.
        $current = Deal::find($this->editingId)?->stage;
        $allowedStages = $current === Deal::STAGE_WON
            ? [Deal::STAGE_WON, 'potential', 'hot', 'lost']
            : ['potential', 'hot', 'lost'];

        return [
            'editForm.customer_id' => 'required|exists:customers,id',
            'editForm.deal_date' => 'required|date',
            'editForm.request_source' => 'nullable|string',
            'editForm.assigned_staff_id' => 'nullable|exists:users,id',
            'editForm.stage' => ['required', Rule::in($allowedStages)],
            'editForm.additional_details' => 'nullable|string',
        ];
    }

    public function saveEdit(): void
    {
        $this->validate($this->editRules());

        $deal = Deal::findOrFail($this->editingId);

        // Belt-and-braces alongside the rule above: once a deal is won, its
        // stage can't be edited away from here either — see Deals\Edit::save().
        $data = $this->editForm;
        $data['stage'] = $deal->stage === Deal::STAGE_WON ? Deal::STAGE_WON : $data['stage'];

        $deal->update($data);

        $this->editingId = null;
        session()->flash('status', 'Deal updated.');
    }

    public function deleteDeal(int $id): void
    {
        $deal = Deal::findOrFail($id);

        $isOwner = $deal->assigned_staff_id === auth()->id() || $deal->created_by === auth()->id();
        abort_unless(auth()->user()->canManageAllRecords() || $isOwner, 403);

        $deal->delete();

        $this->viewingId = null;
        $this->editingId = null;
        session()->flash('status', 'Deal deleted.');
    }

    public function convertToQuotation(int $id)
    {
        return redirect()->route('crm.quotations.create', ['dealId' => $id]);
    }

    public function render()
    {
        $query = $this->baseQuery();

        match ($this->tab) {
            'in_progress' => $query->whereIn('stage', ['potential', 'hot'])->whereNull('converted_at'),
            'converted' => $query->where('stage', 'won'),
            'expired' => $query->whereIn('stage', ['potential', 'hot'])->whereNull('converted_at')->where('expires_at', '<', now()),
            default => null,
        };

        $deals = $query->orderByDesc('created_at')->paginate(15);

        return view('crm.deals.index', [
            'deals' => $deals,
            'customers' => Customer::orderBy('company_name')->limit(300)->pluck('company_name', 'id'),
            'requestSources' => SettingOption::options(SettingOption::REQUEST_SOURCE),
            'staff' => User::crmStaff()->orderBy('name')->pluck('name', 'id'),
        ])->layout('layouts.crm');
    }
}
