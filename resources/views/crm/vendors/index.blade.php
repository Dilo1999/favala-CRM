<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Vendors</h1>
        <button wire:click="create" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">
            <x-heroicon-o-plus class="w-4 h-4" /> Add Vendor
        </button>
    </div>

    <div class="relative mb-4 max-w-md">
        <x-heroicon-o-search class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by name, contact or phone…"
            class="w-full pl-9 rounded-lg bg-gray-900 border-gray-700 text-white text-sm" />
    </div>

    <div class="rounded-xl border border-gray-800 bg-gray-900 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase border-b border-gray-800">
                    <th class="p-3">Company Name</th>
                    <th class="p-3">Contact</th>
                    <th class="p-3">Phone</th>
                    <th class="p-3">Location</th>
                    <th class="p-3">Priced Products</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse ($vendors as $vendor)
                    <tr class="hover:bg-gray-800/40">
                        <td class="p-3 text-white font-medium">{{ $vendor->company_name }}</td>
                        <td class="p-3 text-gray-300">{{ $vendor->contact_person }}</td>
                        <td class="p-3 text-gray-300">{{ $vendor->phone }}</td>
                        <td class="p-3 text-gray-400">{{ $vendor->location }}</td>
                        <td class="p-3 text-gray-400">{{ $vendor->prices_count }}</td>
                        <td class="p-3 text-right">
                            <x-row-menu>
                                <button wire:click="edit({{ $vendor->id }})" class="block w-full text-left px-3 py-1.5 text-gray-200 hover:bg-gray-700">Edit</button>
                                <button wire:click="delete({{ $vendor->id }})" wire:confirm="Delete this vendor?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-gray-700">Delete</button>
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-gray-500">No vendors found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $vendors->links() }}</div>

    @if ($showForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showForm', false)">
            <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-lg p-6">
                <h2 class="text-lg font-bold text-white mb-4">{{ $editingId ? 'Edit Vendor' : 'Add Vendor' }}</h2>
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
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-400 mb-1">Location</label>
                        <input type="text" wire:model="form.location" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                    </div>
                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Save Vendor</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
