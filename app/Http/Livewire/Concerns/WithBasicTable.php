<?php

namespace App\Http\Livewire\Concerns;

use Livewire\WithPagination;

/**
 * Shared search + sort + pagination behaviour for the CRM's list pages, so every
 * module (Leads, Vendors, Products, Tasks, …) gets the same table UX for free.
 */
trait WithBasicTable
{
    use WithPagination;

    public string $search = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    protected string $paginationTheme = 'tailwind';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
}
