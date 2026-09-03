<?php

namespace App\Http\Livewire\Crm\Deals;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\SettingOption;
use App\Models\User;
use Livewire\Component;

class Edit extends Component
{
    public Deal $record;

    public int $customer_id;

    public string $deal_date;

    public ?string $request_source = null;

    public ?int $assigned_staff_id = null;

    public string $stage;

    public ?string $additional_details = null;

    public function mount(Deal $record): void
    {
        $this->record = $record;
        $this->customer_id = $record->customer_id;
        $this->deal_date = $record->deal_date->toDateString();
        $this->request_source = $record->request_source;
        $this->assigned_staff_id = $record->assigned_staff_id;
        $this->stage = $record->stage;
        $this->additional_details = $record->additional_details;
    }

    protected function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'deal_date' => 'required|date',
            'request_source' => 'nullable|string',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'stage' => 'required|in:potential,hot,lost,won',
            'additional_details' => 'nullable|string',
        ];
    }

    public function save()
    {
        $this->validate();

        $this->record->update([
            'customer_id' => $this->customer_id,
            'deal_date' => $this->deal_date,
            'request_source' => $this->request_source,
            'assigned_staff_id' => $this->assigned_staff_id,
            'stage' => $this->stage,
            'additional_details' => $this->additional_details,
        ]);

        session()->flash('status', 'Deal updated.');

        return redirect()->route('crm.deals.show', $this->record);
    }

    public function delete()
    {
        $this->record->delete();

        return redirect()->route('crm.deals');
    }

    public function render()
    {
        return view('crm.deals.edit', [
            'customers' => Customer::orderBy('company_name')->limit(300)->pluck('company_name', 'id'),
            'requestSources' => SettingOption::options(SettingOption::REQUEST_SOURCE),
            'staff' => User::crmStaff()->orderBy('name')->pluck('name', 'id'),
        ])->layout('layouts.crm');
    }
}
