<?php

namespace App\Http\Livewire\Crm\Tasks;

use App\Models\SettingOption;
use App\Models\Task;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $view = 'assigned';

    public string $search = '';

    public string $completedSearch = '';

    public bool $showForm = false;

    public array $form = ['type' => null, 'customer_id' => null, 'assigned_to' => null, 'deadline' => '', 'notes' => ''];

    protected function rules(): array
    {
        return [
            'form.type' => 'required|string',
            'form.customer_id' => 'required|exists:customers,id',
            'form.assigned_to' => 'required|exists:users,id',
            'form.deadline' => 'required|date',
            'form.notes' => 'nullable|string',
        ];
    }

    public function create(): void
    {
        $this->form = ['type' => null, 'customer_id' => null, 'assigned_to' => null, 'deadline' => now()->toDateString(), 'notes' => ''];
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();
        Task::create($this->form + ['status' => 'pending']);
        $this->showForm = false;
        session()->flash('status', 'Task created.');
    }

    public function complete(int $id): void
    {
        Task::findOrFail($id)->markComplete(auth()->user());
        session()->flash('status', 'Task marked complete.');
    }

    public function delete(int $id): void
    {
        Task::findOrFail($id)->delete();
        session()->flash('status', 'Task deleted.');
    }

    public function render()
    {
        $assigned = Task::with(['customer', 'assignedTo'])
            ->where('status', 'pending')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->whereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->search}%"))
                    ->orWhere('type', 'like', "%{$this->search}%");
            }))
            ->orderBy('deadline')
            ->paginate(15, ['*'], 'assignedPage');

        $completed = Task::with(['customer', 'completedBy'])
            ->where('status', 'completed')
            ->when($this->completedSearch, fn ($q) => $q->whereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$this->completedSearch}%")))
            ->orderByDesc('completed_on')
            ->paginate(15, ['*'], 'completedPage');

        return view('crm.tasks.index', [
            'assigned' => $assigned,
            'completed' => $completed,
            'taskTypes' => SettingOption::options(SettingOption::TASK_TYPE),
            'customers' => \App\Models\Customer::orderBy('company_name')->limit(300)->pluck('company_name', 'id'),
            'staff' => User::crmStaff()->orderBy('name')->pluck('name', 'id'),
        ])->layout('layouts.crm');
    }
}
