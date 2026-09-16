<?php

namespace App\Http\Livewire\Crm\Deals;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\SettingOption;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public Deal $record;

    public int $customer_id;

    public string $deal_date;

    public ?string $request_source = null;

    // Deliberately untyped: bound live via wire:model to a <select> whose
    // "Unassigned" option submits "", and PHP's typed-property coercion rejects
    // "" => ?int with an uncaught TypeError before validation ever runs.
    public $assigned_staff_id = null;

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

    /** Normalizes the "Unassigned" option's "" back to null so it never reaches save() as an empty string. */
    public function updatedAssignedStaffId($value): void
    {
        if ($value === '') {
            $this->assigned_staff_id = null;
        }
    }

    protected function rules(): array
    {
        // "won" is reached only through Quotation::convertToInvoice() ->
        // markWon(), which also stamps converted_at — this form's stage
        // field is display-only ("Won (automatic)") once a deal is already
        // won, so it's not a value this form should ever be able to submit
        // for a deal that isn't won yet.
        $allowedStages = $this->record->stage === Deal::STAGE_WON
            ? [Deal::STAGE_WON, 'potential', 'hot', 'lost']
            : ['potential', 'hot', 'lost'];

        return [
            'customer_id' => 'required|exists:customers,id',
            'deal_date' => 'required|date',
            'request_source' => 'nullable|string',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'stage' => ['required', Rule::in($allowedStages)],
            'additional_details' => 'nullable|string',
        ];
    }

    public function save()
    {
        $this->validate();

        // Belt-and-braces alongside the rule above: once a deal is won, its
        // stage can't be edited away from here either — that would desync it
        // from the invoice that actually won it, the same way editing a
        // converted quotation after the fact would.
        $stage = $this->record->stage === Deal::STAGE_WON ? Deal::STAGE_WON : $this->stage;

        $this->record->update([
            'customer_id' => $this->customer_id,
            'deal_date' => $this->deal_date,
            'request_source' => $this->request_source,
            'assigned_staff_id' => $this->assigned_staff_id !== null ? (int) $this->assigned_staff_id : null,
            'stage' => $stage,
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
