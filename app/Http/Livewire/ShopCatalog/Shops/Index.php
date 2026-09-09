<?php

namespace App\Http\Livewire\ShopCatalog\Shops;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\ShopCatalog\Shop;
use App\Services\ShopCatalogSync;
use Livewire\Component;

class Index extends Component
{
    use WithBasicTable;

    public bool $showForm = false;

    public ?int $editingId = null;

    public array $form = ['name' => '', 'contact_person' => '', 'phone' => '', 'location' => ''];

    protected function rules(): array
    {
        return [
            'form.name' => 'required|string|max:191',
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
        $this->form = Shop::findOrFail($id)->only(array_keys($this->form));
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $shop = $this->editingId
            ? tap(Shop::findOrFail($this->editingId))->update($this->form)
            : Shop::create($this->form);

        // Push straight to the CRM's Vendor list, same as a product gets pushed
        // to the main catalog — no need to wait for a search pick elsewhere.
        app(ShopCatalogSync::class)->syncShop($shop);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('status', 'Shop saved.');
    }

    public function delete(int $id): void
    {
        Shop::findOrFail($id)->delete();
        session()->flash('status', 'Shop deleted.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->form = ['name' => '', 'contact_person' => '', 'phone' => '', 'location' => ''];
    }

    public function render()
    {
        $shops = Shop::with('prices.product')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('contact_person', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            }))
            ->orderBy($this->sortField === 'created_at' ? 'name' : $this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('shop-catalog.shops.index', ['shops' => $shops])->layout('layouts.shop-catalog');
    }
}
