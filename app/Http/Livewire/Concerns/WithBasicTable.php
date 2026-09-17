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

    /**
     * Columns a using component's render() is actually prepared to sort
     * by — sortBy() is a public Livewire method, callable directly (not
     * just from a wired-up column header) with any string, and several
     * components feed $sortField straight into orderBy() with nothing else
     * checking it first. Override this per component to add real columns.
     */
    protected function sortableFields(): array
    {
        return ['created_at'];
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->sortableFields(), true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
}
