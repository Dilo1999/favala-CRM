<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Vendors</h1>
        <button wire:click="create" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
            <x-heroicon-o-plus class="w-4 h-4" /> Add Vendor
        </button>
    </div>

    <div class="rounded-xl border border-white/10 bg-zinc-800 p-6">
        <h2 class="text-lg font-bold text-white">Vendor List</h2>
        <p class="text-sm text-zinc-500 mt-1">Manage your suppliers and their details.</p>

        <div class="relative mt-4 mb-5 max-w-md">
            <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.debounce.400ms="search" placeholder="Search by name, contact, phone…"
                class="w-full pl-9 rounded-lg bg-zinc-900 border-white/10 text-white text-sm" />
        </div>

        <div class="overflow-x-auto -mx-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                        <th class="px-6 py-3">Company Name</th>
                        <th class="px-6 py-3">Contact</th>
                        <th class="px-6 py-3">Phone</th>
                        <th class="px-6 py-3">Location</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($vendors as $vendor)
                        <tr class="hover:bg-zinc-700/40">
                            <td class="px-6 py-3 text-white font-medium">{{ $vendor->company_name }}</td>
                            <td class="px-6 py-3 text-zinc-300">{{ $vendor->contact_person }}</td>
                            <td class="px-6 py-3 text-zinc-300">{{ $vendor->phone }}</td>
                            <td class="px-6 py-3 text-zinc-400">{{ $vendor->location }}</td>
                            <td class="px-6 py-3 text-right">
                                <x-row-menu>
                                    <button wire:click="edit({{ $vendor->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                                    <button wire:click="delete({{ $vendor->id }})" wire:confirm="Delete this vendor?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                                </x-row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-6 text-center text-zinc-500">No vendors found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $vendors->links() }}</div>

    @if ($showForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showForm', false)">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-lg p-6">
                <h2 class="text-lg font-bold text-white mb-4">{{ $editingId ? 'Edit Vendor' : 'Add Vendor' }}</h2>
                <form wire:submit.prevent="save" class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs text-zinc-400 mb-1">Company Name</label>
                        <input type="text" wire:model="form.company_name" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                        @error('form.company_name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Contact Person</label>
                        <input type="text" wire:model="form.contact_person" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Phone</label>
                        <input type="text" wire:model="form.phone" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-zinc-400 mb-1">Location</label>
                        <input type="text" wire:model="form.location" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                    </div>
                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Save Vendor</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
