<div>
    @if ($showForm)
        {{-- Full-page Create / Edit form --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-white">{{ $editingId ? 'Edit Customer' : 'Create Customer' }}</h1>
        </div>

        <div class="rounded-xl border border-white/10 bg-zinc-800 p-6">
            <h2 class="text-lg font-bold text-white">Lead Details</h2>
            <p class="text-sm text-zinc-500 mt-1">Enter the details for the new lead.</p>

            <form wire:submit.prevent="save" class="grid grid-cols-2 gap-x-8 gap-y-5 mt-6">
                <div>
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Company Name</label>
                    <input type="text" wire:model="form.company_name" placeholder="e.g., Island Builders"
                        class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5 placeholder-zinc-600" />
                    @error('form.company_name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Contact Person</label>
                    <input type="text" wire:model="form.contact_person" placeholder="e.g., Ahmed Ali"
                        class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5 placeholder-zinc-600" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Phone</label>
                    <input type="text" wire:model="form.phone" placeholder="e.g., 777-1234"
                        class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5 placeholder-zinc-600" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Tax ID</label>
                    <input type="text" wire:model="form.tin" placeholder="e.g., TIN12345678"
                        class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5 placeholder-zinc-600" />
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Atoll</label>
                    <select wire:model="form.atoll_id" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5">
                        <option value="">Select an atoll</option>
                        @foreach ($atolls as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Island / Resort</label>
                    <select wire:model="form.island_id" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5">
                        <option value="">Select an island</option>
                        @foreach ($this->islands as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Address</label>
                    <textarea wire:model="form.address" placeholder="Enter the full address" rows="3"
                        class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5 placeholder-zinc-600 resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Customer Type</label>
                    <select wire:model="form.customer_type" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5">
                        <option value="">Select a type</option>
                        @foreach ($customerTypes as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Lead Source</label>
                    <select wire:model="form.lead_source" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5">
                        <option value="">Select a source</option>
                        @foreach ($leadSources as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Assigned Staff</label>
                    <select wire:model="form.assigned_staff_id" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5">
                        <option value="">Unassigned</option>
                        @foreach ($staff as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Lead Status</label>
                    <select wire:model="form.status" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5">
                        @foreach ($leadStatuses as $key => $label)
                            <option value="{{ is_int($key) ? $label : $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-200 mb-2">Date Added</label>
                    <div class="relative">
                        <input type="date" wire:model="form.date_added"
                            class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5" />
                    </div>
                    @error('form.date_added') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2 flex justify-end gap-2 mt-4 pt-4 border-t border-white/10">
                    <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm font-medium hover:bg-zinc-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">{{ $editingId ? 'Save Changes' : 'Create Lead' }}</button>
                </div>
            </form>
        </div>
    @else
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Leads</h1>
        <div class="flex gap-2">
            <button wire:click="$set('showImport', true)" class="flex items-center gap-2 px-4 py-2 rounded-lg border border-white/10 text-zinc-200 text-sm font-medium hover:bg-zinc-700">
                <x-heroicon-o-upload class="w-4 h-4" /> Import Leads
            </button>
            <button wire:click="create" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
                <x-heroicon-o-plus-circle class="w-4 h-4" /> Add Lead
            </button>
        </div>
    </div>

    <div class="rounded-xl border border-white/10 bg-zinc-800 p-6">
        <h2 class="text-lg font-bold text-white">Lead List</h2>
        <p class="text-sm text-zinc-500 mt-1">Manage your leads and view their details.</p>

        <div class="flex flex-wrap gap-3 mt-4 mb-5">
            <div class="relative flex-1 min-w-[240px]">
                <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.debounce.400ms="search" placeholder="Search by name, phone, or island…"
                    class="w-full pl-9 rounded-lg bg-zinc-900 border-white/10 text-white text-sm" />
            </div>
            <select wire:model="statusFilter" class="rounded-lg bg-zinc-900 border-white/10 text-white text-sm">
                <option value="">All Statuses</option>
                @foreach ($leadStatuses as $key => $label)
                    <option value="{{ is_int($key) ? $label : $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto -mx-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                        <th class="px-6 py-3">Date Added</th>
                        <th class="px-6 py-3">Company Name</th>
                        <th class="px-6 py-3">Contact Person</th>
                        <th class="px-6 py-3">Phone</th>
                        <th class="px-6 py-3">Atoll</th>
                        <th class="px-6 py-3">Island</th>
                        <th class="px-6 py-3">Added By</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Assigned Staff</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($leads as $lead)
                        <tr class="hover:bg-zinc-700/40">
                            <td class="px-6 py-3 text-zinc-400 whitespace-nowrap">{{ $lead->created_at->format('d/m/Y g:i A') }}</td>
                            <td class="px-6 py-3 text-white font-medium">{{ $lead->company_name }}</td>
                            <td class="px-6 py-3 text-zinc-300">{{ $lead->contact_person }}</td>
                            <td class="px-6 py-3 text-zinc-300">{{ $lead->phone }}</td>
                            <td class="px-6 py-3 text-zinc-400">{{ $lead->atoll?->name }}</td>
                            <td class="px-6 py-3 text-zinc-400">{{ $lead->island?->name }}</td>
                            <td class="px-6 py-3 text-zinc-400">{{ $lead->addedBy?->name }}</td>
                            <td class="px-6 py-3">
                                @php($colors = ['new' => 'blue', 'potential' => 'orange', 'not_qualified' => 'red', 'customer' => 'green'])
                                <x-badge :color="$colors[$lead->status] ?? 'gray'">{{ \App\Models\Customer::STATUSES[$lead->status] ?? $lead->status }}</x-badge>
                            </td>
                            <td class="px-6 py-3 text-zinc-400">{{ $lead->assignedStaff?->name ?? 'Unassigned' }}</td>
                            <td class="px-6 py-3 text-right">
                                <x-row-menu>
                                    <button wire:click="edit({{ $lead->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                                    <button wire:click="delete({{ $lead->id }})" wire:confirm="Delete this lead?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                                </x-row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-6 py-6 text-center text-zinc-500">No leads found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>
    @endif

    {{-- Import modal --}}
    @if ($showImport)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showImport', false)">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-white mb-4">Import Leads</h2>
                <p class="text-xs text-zinc-500 mb-3">CSV columns: company_name, contact_person, phone</p>
                <form wire:submit.prevent="importLeads">
                    <input type="file" wire:model="importFile" class="w-full text-sm text-zinc-300 mb-3" />
                    @error('importFile') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showImport', false)" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Import</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
