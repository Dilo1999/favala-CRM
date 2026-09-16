<?php

namespace App\Http\Livewire\Crm\Queries;

use App\Models\SalesQuery;
use App\Models\SettingOption;
use App\Models\User;
use Livewire\Component;

class Detail extends Component
{
    public SalesQuery $record;

    public string $status = SalesQuery::STATUS_NEW;

    public bool $followUp = false;

    public ?string $description = null;

    public ?string $phone = null;

    public ?string $querySource = null;

    public ?string $queryType = null;

    public ?string $productCategory = null;

    public $assignedStaffId = null;

    public function mount(SalesQuery $record): void
    {
        $this->record = $record->load(['customer', 'deal', 'quotation', 'assignedStaff']);
        $this->status = $record->status;
        $this->followUp = $record->follow_up;
        $this->description = $record->description;
        $this->phone = $record->phone;
        $this->querySource = $record->query_source;
        $this->queryType = $record->query_type;
        $this->assignedStaffId = $record->assigned_staff_id;

        $categoryOptions = SettingOption::options(SettingOption::PRODUCT_CATEGORY);
        $this->productCategory = collect($record->tags ?? [])
            ->first(fn ($tag) => array_key_exists($tag, $categoryOptions));
    }

    public function save(): void
    {
        $this->validate([
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:60',
            'assignedStaffId' => 'nullable|exists:users,id',
        ]);

        $categoryOptions = array_keys(SettingOption::options(SettingOption::PRODUCT_CATEGORY));

        $tags = collect($this->record->tags ?? [])
            ->reject(fn ($tag) => in_array($tag, $categoryOptions, true))
            ->when($this->productCategory, fn ($tags) => $tags->push($this->productCategory))
            ->unique()
            ->values()
            ->all();

        $this->record->update([
            'status' => $this->status,
            'follow_up' => $this->followUp,
            'description' => $this->description,
            'phone' => $this->phone,
            'query_source' => $this->querySource,
            'query_type' => $this->queryType,
            'assigned_staff_id' => $this->assignedStaffId,
            'tags' => $tags,
        ]);

        session()->flash('status', 'Query updated.');
    }

    public function delete()
    {
        abort_unless(auth()->user()->canManageAllRecords() || $this->record->assigned_staff_id === auth()->id(), 403);

        $this->record->delete();

        session()->flash('status', 'Query deleted.');

        return redirect()->route('crm.queries');
    }

    public function render()
    {
        return view('crm.queries.detail', [
            'staff' => User::crmStaff()->orderBy('name')->pluck('name', 'id'),
            'querySources' => SettingOption::options(SettingOption::QUERY_SOURCE),
            'queryTypes' => SettingOption::options(SettingOption::QUERY_TYPE),
            'productCategories' => SettingOption::options(SettingOption::PRODUCT_CATEGORY),
        ])->layout('layouts.crm');
    }
}
