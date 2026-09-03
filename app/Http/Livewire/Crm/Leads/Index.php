<?php

namespace App\Http\Livewire\Crm\Leads;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Atoll;
use App\Models\Customer;
use App\Models\Island;
use App\Models\SettingOption;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithBasicTable, WithFileUploads;

    public bool $showForm = false;

    public bool $showImport = false;

    public ?int $editingId = null;

    public array $form = [
        'company_name' => '', 'contact_person' => '', 'phone' => '', 'tin' => '',
        'atoll_id' => null, 'island_id' => null, 'address' => '',
        'customer_type' => null, 'lead_source' => null, 'assigned_staff_id' => null, 'status' => 'new',
    ];

    public string $statusFilter = '';

    public $importFile;

    protected function rules(): array
    {
        return [
            'form.company_name' => 'required|string|max:191',
            'form.contact_person' => 'nullable|string|max:191',
            'form.phone' => 'nullable|string|max:60',
            'form.tin' => 'nullable|string|max:60',
            'form.atoll_id' => 'nullable|exists:atolls,id',
            'form.island_id' => 'nullable|exists:islands,id',
            'form.address' => 'nullable|string|max:191',
            'form.customer_type' => 'nullable|string',
            'form.lead_source' => 'nullable|string',
            'form.assigned_staff_id' => 'nullable|exists:users,id',
            'form.status' => 'required|string',
        ];
    }

    public function updatedFormAtollId(): void
    {
        $this->form['island_id'] = null;
    }

    public function getIslandsProperty()
    {
        return $this->form['atoll_id'] ? Island::where('atoll_id', $this->form['atoll_id'])->orderBy('name')->pluck('name', 'id') : collect();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $this->editingId = $id;
        $this->form = $customer->only(array_keys($this->form));
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            Customer::findOrFail($this->editingId)->update($this->form);
        } else {
            Customer::create($this->form + ['added_by' => auth()->id()]);
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('status', 'Lead saved.');
    }

    public function delete(int $id): void
    {
        Customer::findOrFail($id)->delete();
        session()->flash('status', 'Lead deleted.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->form = [
            'company_name' => '', 'contact_person' => '', 'phone' => '', 'tin' => '',
            'atoll_id' => null, 'island_id' => null, 'address' => '',
            'customer_type' => null, 'lead_source' => null, 'assigned_staff_id' => null, 'status' => 'new',
        ];
    }

    public function importLeads(): void
    {
        $this->validate(['importFile' => 'required|file|mimes:csv,txt']);

        $path = $this->importFile->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_map('strtolower', array_map('trim', array_shift($rows)));

        $count = 0;
        foreach ($rows as $row) {
            if (count($row) < count($header)) {
                continue;
            }
            $record = array_combine($header, $row);
            if (empty($record['company_name'] ?? null)) {
                continue;
            }
            Customer::create([
                'company_name' => trim($record['company_name']),
                'contact_person' => trim($record['contact_person'] ?? ''),
                'phone' => trim($record['phone'] ?? ''),
                'status' => Customer::STATUS_NEW,
                'added_by' => auth()->id(),
            ]);
            $count++;
        }

        $this->showImport = false;
        $this->importFile = null;
        session()->flash('status', "Imported {$count} leads.");
    }

    public function render()
    {
        $leads = Customer::with(['atoll', 'island', 'assignedStaff'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('company_name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhereHas('island', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('crm.leads.index', [
            'leads' => $leads,
            'atolls' => Atoll::orderBy('name')->pluck('name', 'id'),
            'customerTypes' => SettingOption::options(SettingOption::CUSTOMER_TYPE),
            'leadSources' => SettingOption::options(SettingOption::LEAD_SOURCE),
            'leadStatuses' => SettingOption::options(SettingOption::LEAD_STATUS) ?: Customer::STATUSES,
            'staff' => User::crmStaff()->orderBy('name')->pluck('name', 'id'),
        ])->layout('layouts.crm');
    }
}
