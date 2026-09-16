<?php

namespace App\Http\Livewire\Crm\Activities;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Activity;
use App\Models\SettingOption;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $typeFilter = '';

    public string $statusFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public array $form = ['type' => null, 'customer_id' => null, 'outcome' => null, 'status' => 'follow_up', 'date' => '', 'details' => ''];

    protected function rules(): array
    {
        return [
            'form.type' => 'required|string',
            'form.customer_id' => 'required|exists:customers,id',
            'form.outcome' => 'required|string',
            'form.status' => 'required|string',
            'form.date' => 'required|date',
            'form.details' => 'nullable|string',
        ];
    }

    public function create(): void
    {
        $this->editingId = null;
        $this->form = ['type' => null, 'customer_id' => null, 'outcome' => null, 'status' => 'follow_up', 'date' => now()->toDateString(), 'details' => ''];
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $activity = Activity::findOrFail($id);
        $this->editingId = $id;
        $this->form = [
            'type' => $activity->type,
            'customer_id' => $activity->customer_id,
            'outcome' => $activity->outcome,
            'status' => $activity->status,
            'date' => optional($activity->date)->toDateString(),
            'details' => $activity->details,
        ];
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            Activity::findOrFail($this->editingId)->update($this->form);
            session()->flash('status', 'Activity updated.');
        } else {
            Activity::create($this->form + ['done_by' => auth()->id()]);
            session()->flash('status', 'Activity logged.');
        }

        $this->showForm = false;
        $this->editingId = null;
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->editingId = null;
    }

    public function delete(int $id): void
    {
        $activity = Activity::findOrFail($id);

        abort_unless(auth()->user()->canManageAllRecords() || $activity->done_by === auth()->id(), 403);

        $activity->delete();
        session()->flash('status', 'Activity deleted.');
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'typeFilter', 'statusFilter', 'dateFrom', 'dateTo']);
    }

    public function render()
    {
        $activities = Activity::with(['customer', 'doneBy'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('outcome', 'like', "%{$this->search}%")
                    ->orWhere('details', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%")->orWhere('phone', 'like', "%{$this->search}%"));
            }))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->orderByDesc('date')
            ->paginate(15);

        return view('crm.activities.index', [
            'activities' => $activities,
            'outcomes' => SettingOption::options(SettingOption::ACTIVITY_OUTCOME),
            'customers' => \App\Models\Customer::orderBy('company_name')->limit(300)->pluck('company_name', 'id'),
        ])->layout('layouts.crm');
    }
}
