<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Leads</h1>
        <div class="flex gap-2">
            <button wire:click="$set('showImport', true)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm font-medium hover:bg-gray-800">
                Import Leads
            </button>
            <button wire:click="create" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">
                <x-heroicon-o-plus class="w-4 h-4" /> Add Lead
            </button>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
        <div class="relative flex-1 min-w-[240px]">
            <x-heroicon-o-search class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.debounce.400ms="search" placeholder="Search by name, phone or island…"
                class="w-full pl-9 rounded-lg bg-gray-900 border-gray-700 text-white text-sm" />
        </div>
        <select wire:model="statusFilter" class="rounded-lg bg-gray-900 border-gray-700 text-white text-sm">
            <option value="">All Statuses</option>
            @foreach ($leadStatuses as $key => $label)
                <option value="{{ is_int($key) ? $label : $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="rounded-xl border border-gray-800 bg-gray-900 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase border-b border-gray-800">
                    <th class="p-3">Date Added</th>
                    <th class="p-3">Company Name</th>
                    <th class="p-3">Contact Person</th>
                    <th class="p-3">Phone</th>
                    <th class="p-3">Atoll</th>
                    <th class="p-3">Island</th>
                    <th class="p-3">Added By</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Assigned Staff</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse ($leads as $lead)
                    <tr class="hover:bg-gray-800/40">
                        <td class="p-3 text-gray-400">{{ $lead->created_at->format('d M Y') }}</td>
                        <td class="p-3 text-white font-medium">{{ $lead->company_name }}</td>
                        <td class="p-3 text-gray-300">{{ $lead->contact_person }}</td>
                        <td class="p-3 text-gray-300">{{ $lead->phone }}</td>
                        <td class="p-3 text-gray-400">{{ $lead->atoll?->name }}</td>
                        <td class="p-3 text-gray-400">{{ $lead->island?->name }}</td>
                        <td class="p-3 text-gray-400">{{ $lead->addedBy?->name }}</td>
                        <td class="p-3">
                            @php($colors = ['new' => 'gray', 'potential' => 'orange', 'not_qualified' => 'red', 'customer' => 'green'])
                            <x-badge :color="$colors[$lead->status] ?? 'gray'">{{ \App\Models\Customer::STATUSES[$lead->status] ?? $lead->status }}</x-badge>
                        </td>
                        <td class="p-3 text-gray-400">{{ $lead->assignedStaff?->name ?? 'Unassigned' }}</td>
                        <td class="p-3 text-right">
                            <x-row-menu>
                                <button wire:click="edit({{ $lead->id }})" class="block w-full text-left px-3 py-1.5 text-gray-200 hover:bg-gray-700">Edit</button>
                                <button wire:click="delete({{ $lead->id }})" wire:confirm="Delete this lead?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-gray-700">Delete</button>
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="p-6 text-center text-gray-500">No leads found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>

    {{-- Create / Edit modal --}}
    @if ($showForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showForm', false)">
            <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-bold text-white mb-4">{{ $editingId ? 'Edit Lead' : 'Add Lead' }}</h2>
                <form wire:submit.prevent="save" class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-400 mb-1">Company Name</label>
                        <input type="text" wire:model="form.company_name" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                        @error('form.company_name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Contact Person</label>
                        <input type="text" wire:model="form.contact_person" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Phone</label>
                        <input type="text" wire:model="form.phone" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Tax ID (TIN)</label>
                        <input type="text" wire:model="form.tin" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Customer Type</label>
                        <select wire:model="form.customer_type" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($customerTypes as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Atoll</label>
                        <select wire:model="form.atoll_id" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($atolls as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Island / Resort</label>
                        <select wire:model="form.island_id" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($this->islands as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-400 mb-1">Address</label>
                        <input type="text" wire:model="form.address" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Lead Source</label>
                        <select wire:model="form.lead_source" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            <option value="">Select…</option>
                            @foreach ($leadSources as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Assigned Staff</label>
                        <select wire:model="form.assigned_staff_id" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            <option value="">Unassigned</option>
                            @foreach ($staff as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-400 mb-1">Lead Status</label>
                        <select wire:model="form.status" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                            @foreach ($leadStatuses as $key => $label)
                                <option value="{{ is_int($key) ? $label : $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Save Lead</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Import modal --}}
    @if ($showImport)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showImport', false)">
            <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-white mb-4">Import Leads</h2>
                <p class="text-xs text-gray-500 mb-3">CSV columns: company_name, contact_person, phone</p>
                <form wire:submit.prevent="importLeads">
                    <input type="file" wire:model="importFile" class="w-full text-sm text-gray-300 mb-3" />
                    @error('importFile') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showImport', false)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Import</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
