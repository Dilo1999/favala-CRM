<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Activities</h1>
        <button wire:click="create" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">
            <x-heroicon-o-plus class="w-4 h-4" /> Log Activity
        </button>
    </div>

    <div class="flex flex-wrap gap-3 mb-4 items-end">
        <div class="relative flex-1 min-w-[220px]">
            <x-heroicon-o-search class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.debounce.400ms="search" placeholder="Search customer, outcome, phone…" class="w-full pl-9 rounded-lg bg-gray-900 border-gray-700 text-white text-sm" />
        </div>
        <input type="date" wire:model="dateFrom" class="rounded-lg bg-gray-900 border-gray-700 text-white text-sm" />
        <input type="date" wire:model="dateTo" class="rounded-lg bg-gray-900 border-gray-700 text-white text-sm" />
        <select wire:model="typeFilter" class="rounded-lg bg-gray-900 border-gray-700 text-white text-sm">
            <option value="">All Types</option>
            <option value="Call">Call</option><option value="Meeting">Meeting</option><option value="Email">Email</option><option value="Visit">Visit</option>
        </select>
        <select wire:model="statusFilter" class="rounded-lg bg-gray-900 border-gray-700 text-white text-sm">
            <option value="">All Statuses</option>
            <option value="follow_up">Follow-up</option><option value="closed">Closed</option>
        </select>
        <button wire:click="clearFilters" class="px-3 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Clear Filters</button>
    </div>

    <div class="rounded-xl border border-gray-800 bg-gray-900 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase border-b border-gray-800">
                    <th class="p-3">Type</th><th class="p-3">Customer</th><th class="p-3">Outcome & Details</th><th class="p-3">Status</th><th class="p-3">Done By</th><th class="p-3">Date</th><th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse ($activities as $activity)
                    <tr class="hover:bg-gray-800/40">
                        <td class="p-3 text-white">{{ $activity->type }}</td>
                        <td class="p-3 text-gray-300">{{ $activity->customer?->company_name }}<span class="block text-xs text-gray-500">{{ $activity->customer?->phone }}</span></td>
                        <td class="p-3 text-gray-400 max-w-xs">
                            <span class="text-gray-200">{{ $activity->outcome }}</span>
                            <span class="block text-xs truncate">{{ $activity->details }}</span>
                        </td>
                        <td class="p-3"><x-badge :color="$activity->status === 'closed' ? 'green' : 'orange'">{{ $activity->status === 'closed' ? 'Closed' : 'Follow-up' }}</x-badge></td>
                        <td class="p-3 text-gray-400">{{ $activity->doneBy?->name }}</td>
                        <td class="p-3 text-gray-400">{{ optional($activity->date)->format('d M Y') }}</td>
                        <td class="p-3 text-right">
                            <x-row-menu>
                                <button wire:click="delete({{ $activity->id }})" wire:confirm="Delete this activity?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-gray-700">Delete</button>
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-6 text-center text-gray-500">No activities found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $activities->links() }}</div>

    @if ($showForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showForm', false)">
            <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-lg p-6">
                <h2 class="text-lg font-bold text-white mb-4">Log Activity</h2>
                <form wire:submit.prevent="save" class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Type</label>
                        <select wire:model="form.type" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            <option value="">Select…</option>
                            <option value="Call">Call</option><option value="Meeting">Meeting</option><option value="Email">Email</option><option value="Visit">Visit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Date</label>
                        <input type="date" wire:model="form.date" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-400 mb-1">Customer</label>
                        <select wire:model="form.customer_id" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Outcome</label>
                        <select wire:model="form.outcome" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($outcomes as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Status</label>
                        <select wire:model="form.status" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            <option value="follow_up">Follow-up</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-400 mb-1">Details</label>
                        <textarea wire:model="form.details" rows="3" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm"></textarea>
                    </div>
                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Save Activity</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
