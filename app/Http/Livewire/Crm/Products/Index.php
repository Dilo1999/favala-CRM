<?php

namespace App\Http\Livewire\Crm\Products;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Product;
use App\Models\SettingOption;
use App\Services\CrmTestProductsClient;
use App\Services\ShopCatalogSync;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithBasicTable, WithFileUploads;

    /**
     * Pull every Shop Catalog product (and its current prices) into the local
     * catalog on every visit to this page, so the list here always reflects
     * both the local products AND the external Shop Catalog — not just the
     * ones someone happened to already pick via product search elsewhere.
     */
    public function mount(ShopCatalogSync $sync): void
    {
        $sync->syncAll();
    }

    public bool $showForm = false;

    public bool $showImport = false;

    public ?int $editingId = null;

    public array $form = ['code' => '', 'legacy_code' => '', 'description' => '', 'category' => null, 'brand' => '', 'unit_of_measure' => ''];

    public $importFile;

    protected function rules(): array
    {
        return [
            'form.code' => 'required|string|max:50',
            'form.legacy_code' => 'nullable|string|max:100',
            'form.description' => 'required|string|max:191',
            'form.category' => 'nullable|string',
            'form.brand' => 'nullable|string|max:100',
            'form.unit_of_measure' => 'nullable|string|max:30',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->form['code'] = 'FAVD-'.str_pad((string) (Product::max('id') + 1), 6, '0', STR_PAD_LEFT);
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->editingId = $id;
        $this->form = Product::findOrFail($id)->only(array_keys($this->form));
        $this->showForm = true;
    }

    public function save(ShopCatalogSync $sync): void
    {
        $this->validate();

        $product = $this->editingId
            ? tap(Product::findOrFail($this->editingId))->update($this->form)
            : Product::create($this->form);

        // Keep the Shop Catalog in step too — if this product originally came
        // from there (same code), editing it here should update it there too,
        // not just leave the two catalogs to drift apart.
        $sync->pushProductDetails($product);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('status', 'Product saved.');
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
        session()->flash('status', 'Product deleted.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->form = ['code' => '', 'legacy_code' => '', 'description' => '', 'category' => null, 'brand' => '', 'unit_of_measure' => ''];
    }

    public function importProducts(): void
    {
        $this->validate(['importFile' => 'required|file|mimes:csv,txt']);

        $rows = array_map('str_getcsv', file($this->importFile->getRealPath()));
        $header = array_map('strtolower', array_map('trim', array_shift($rows)));

        $count = 0;
        $skipped = 0;
        foreach ($rows as $row) {
            // Must match the header column-for-column: array_combine() throws
            // on mismatched lengths, so a row with *more* fields than the
            // header (e.g. an unquoted comma inside a text value) used to
            // crash the whole import instead of just being skipped.
            if (count($row) !== count($header)) {
                $skipped++;

                continue;
            }
            $record = array_combine($header, $row);
            if (empty($record['code'] ?? null)) {
                continue;
            }
            Product::updateOrCreate(
                ['code' => trim($record['code'])],
                [
                    'description' => trim($record['description'] ?? ''),
                    'category' => trim($record['category'] ?? '') ?: null,
                    'brand' => trim($record['brand'] ?? '') ?: null,
                ]
            );
            $count++;
        }

        $this->showImport = false;
        $this->importFile = null;
        $message = "Imported {$count} products.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} row(s) with an unexpected number of columns.";
        }
        session()->flash('status', $message);
    }

    public function render(CrmTestProductsClient $crmTestProducts)
    {
        $products = Product::with('prices.vendor')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")
                    ->orWhere('brand', 'like', "%{$this->search}%");
            }))
            ->orderBy($this->sortField === 'created_at' ? 'description' : $this->sortField, $this->sortDirection)
            ->paginate(15);

        // Source: CRM rows (shop_catalog_product_id is null) are, for testing,
        // displayed from the standalone crm-test-service app instead of straight
        // off this model — Source: Shop Catalog rows are left completely untouched.
        $apiProducts = $crmTestProducts->all($this->search);

        $products->getCollection()->transform(function (Product $product) use ($apiProducts) {
            if (! $product->shop_catalog_product_id && $apiProducts->has($product->id)) {
                $apiRow = $apiProducts->get($product->id);
                $product->forceFill(collect($apiRow)->only([
                    'code', 'legacy_code', 'description', 'category', 'brand', 'unit_of_measure', 'quantity',
                ])->all());
            }

            return $product;
        });

        return view('crm.products.index', [
            'products' => $products,
            'categories' => SettingOption::options(SettingOption::PRODUCT_CATEGORY),
        ])->layout('layouts.crm');
    }
}
