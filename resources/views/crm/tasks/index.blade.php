<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Tasks</h1>
        <button wire:click="create" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
            <x-heroicon-o-plus class="w-4 h-4" /> Create Task
        </button>
    </div>

    <div class="flex rounded-lg border border-white/10 w-fit mb-6 overflow-hidden text-sm">
        <button wire:click="$set('view', 'assigned')" class="px-4 py-2 font-medium {{ $view === 'assigned' ? 'bg-accent text-white' : 'text-zinc-400' }}">Assigned Tasks</button>
        <button wire:click="$set('view', 'completed')" class="px-4 py-2 font-medium {{ $view === 'completed' ? 'bg-accent text-white' : 'text-zinc-400' }}">Completed Tasks</button>
    </div>

    @if ($view === 'assigned')
        <div class="relative mb-4 max-w-md">
            <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.debounce.400ms="search" placeholder="Search tasks…" class="w-full pl-9 rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
        </div>
        <div class="rounded-xl border border-white/10 bg-zinc-800 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                        <th class="p-3">Type</th><th class="p-3">Lead</th><th class="p-3">Assigned</th><th class="p-3">Deadline</th><th class="p-3">Status</th><th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($assigned as $task)
                        <tr class="hover:bg-zinc-700/40">
                            <td class="p-3 text-white">{{ $task->type }}</td>
                            <td class="p-3 text-zinc-300">{{ $task->customer?->company_name }}</td>
                            <td class="p-3 text-zinc-400">{{ $task->assignedTo?->name }}<span class="block text-xs">{{ $task->created_at->format('d M Y') }}</span></td>
                            <td class="p-3 text-zinc-400">{{ optional($task->deadline)->format('d M Y') }}</td>
                            <td class="p-3"><x-badge color="orange">Pending</x-badge></td>
                            <td class="p-3 text-right">
                                <x-row-menu>
                                    <button wire:click="edit({{ $task->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                                    <button wire:click="complete({{ $task->id }})" class="block w-full text-left px-3 py-1.5 text-green-400 hover:bg-zinc-700">Mark Complete</button>
                                    <button wire:click="delete({{ $task->id }})" wire:confirm="Delete this task?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                                </x-row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-zinc-500">No assigned tasks.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $assigned->links() }}</div>
    @else
        <div class="relative mb-4 max-w-md">
            <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.debounce.400ms="completedSearch" placeholder="Search completed tasks…" class="w-full pl-9 rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
        </div>
        <div class="rounded-xl border border-white/10 bg-zinc-800 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                        <th class="p-3">Type</th><th class="p-3">Lead</th><th class="p-3">Completed By</th><th class="p-3">Completed On</th><th class="p-3">Duration</th><th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($completed as $task)
                        <tr class="hover:bg-zinc-700/40">
                            <td class="p-3 text-white">{{ $task->type }}</td>
                            <td class="p-3 text-zinc-300">{{ $task->customer?->company_name }}</td>
                            <td class="p-3 text-zinc-400">{{ $task->completedBy?->name }}</td>
                            <td class="p-3 text-zinc-400">{{ optional($task->completed_on)->format('d M Y H:i') }}</td>
                            <td class="p-3 text-zinc-400">{{ $task->duration }}</td>
                            <td class="p-3 text-right">
                                <x-row-menu>
                                    <button wire:click="edit({{ $task->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                                    <button wire:click="delete({{ $task->id }})" wire:confirm="Delete this task?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                                </x-row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-zinc-500">No completed tasks.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $completed->links() }}</div>
    @endif

    @if ($showForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="cancel">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-lg p-6">
                <h2 class="text-lg font-bold text-white mb-4">{{ $editingId ? 'Edit Task' : 'Create Task' }}</h2>
                <form wire:submit.prevent="save" class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Task Type</label>
                        <select wire:model="form.type" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($taskTypes as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                        </select>
                        @error('form.type') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Deadline</label>
                        <input type="date" wire:model="form.deadline" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-zinc-400 mb-1">Select Lead</label>
                        <select wire:model="form.customer_id" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                        @error('form.customer_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-zinc-400 mb-1">Assigned To</label>
                        <select wire:model="form.assigned_to" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($staff as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                        @error('form.assigned_to') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-zinc-400 mb-1">Task Notes / Details</label>
                        <textarea wire:model="form.notes" rows="3" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm"></textarea>
                    </div>
                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="cancel" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">{{ $editingId ? 'Save Changes' : 'Save Task' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
