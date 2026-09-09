<?php

namespace App\Http\Livewire\Crm\Vendors;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Vendor;
use App\Services\ShopCatalogSync;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public bool $showForm = false;

    public ?int $editingId = null;

    public array $form = ['company_name' => '', 'contact_person' => '', 'phone' => '', 'location' => ''];

    public function mount(ShopCatalogSync $sync): void
    {
        $sync->syncAllShops();
    }

    protected function rules(): array
    {
        return [
            'form.company_name' => 'required|string|max:191',
            'form.contact_person' => 'nullable|string|max:191',
            'form.phone' => 'nullable|string|max:60',
            'form.location' => 'nullable|string|max:191',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->editingId = $id;
        $this->form = Vendor::findOrFail($id)->only(array_keys($this->form));
        $this->showForm = true;
    }

    public function save(ShopCatalogSync $sync): void
    {
        $this->validate();

        $vendor = $this->editingId
            ? tap(Vendor::findOrFail($this->editingId))->update($this->form)
            : Vendor::create($this->form);

        // Keep the Shop Catalog in step too — if this vendor originally came
        // from there (same name), editing it here should update it there too,
        // not just leave the two sides to drift apart.
        $sync->pushShopDetails($vendor);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('status', 'Vendor saved.');
    }

    public function delete(int $id): void
    {
        Vendor::findOrFail($id)->delete();
        session()->flash('status', 'Vendor deleted.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->form = ['company_name' => '', 'contact_person' => '', 'phone' => '', 'location' => ''];
    }

    public function render()
    {
        $vendors = Vendor::withCount('prices')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('company_name', 'like', "%{$this->search}%")
                    ->orWhere('contact_person', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            }))
            ->orderBy($this->sortField === 'created_at' ? 'company_name' : $this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('crm.vendors.index', ['vendors' => $vendors])->layout('layouts.crm');
    }
}
