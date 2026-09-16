<?php

namespace App\Http\Livewire\Crm\Vendors;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\InvoiceItem;
use App\Models\QuotationItem;
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

    /**
     * invoice_items.vendor_id / quotation_items.vendor_id are nullOnDelete —
     * deleting a vendor that's referenced there doesn't fail, it silently
     * blanks those lines' vendor instead. That's harmless for the document
     * itself, but a return against a blanked line can no longer tell which
     * vendor's stock to restore (SalesReturn::stockLines()), so the restore
     * is quietly skipped when that return is later approved. Blocking the
     * delete here is what actually prevents that, rather than a foreign-key
     * error the database was never set up to raise.
     */
    public function delete(int $id): void
    {
        $vendor = Vendor::findOrFail($id);

        $invoiceLines = InvoiceItem::where('vendor_id', $id)->count();
        $quotationLines = QuotationItem::where('vendor_id', $id)->count();

        if ($invoiceLines > 0 || $quotationLines > 0) {
            $parts = array_filter([
                $invoiceLines > 0 ? "{$invoiceLines} invoice line(s)" : null,
                $quotationLines > 0 ? "{$quotationLines} quotation line(s)" : null,
            ]);

            session()->flash('error', "Can't delete {$vendor->company_name} — it's still referenced by ".implode(' and ', $parts).'. Returns against those sales need this vendor to restore stock correctly.');

            return;
        }

        $vendor->delete();
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
