<?php

namespace App\Http\Livewire\Crm\Products;

use App\Http\Livewire\Concerns\WithBasicTable;
use App\Models\Product;
use App\Models\SettingOption;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithBasicTable, WithFileUploads;

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

    public function save(): void
    {
        $this->validate();

        $this->editingId
            ? Product::findOrFail($this->editingId)->update($this->form)
            : Product::create($this->form);

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
        foreach ($rows as $row) {
            if (count($row) < count($header)) {
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
        session()->flash('status', "Imported {$count} products.");
    }

    public function render()
    {
        $products = Product::with('prices.vendor')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")
                    ->orWhere('brand', 'like', "%{$this->search}%");
            }))
            ->orderBy($this->sortField === 'created_at' ? 'description' : $this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('crm.products.index', [
            'products' => $products,
            'categories' => SettingOption::options(SettingOption::PRODUCT_CATEGORY),
        ])->layout('layouts.crm');
    }
}
