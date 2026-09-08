<div>
    <h1 class="text-2xl font-bold text-white mb-6">Update Pricing for {{ $product->description }}</h1>

    <div class="rounded-xl border border-white/10 bg-zinc-800 p-5">
        <h2 class="text-lg font-bold text-white">Vendor Pricing</h2>
        <p class="text-sm text-zinc-500 mt-1 mb-5">Add or edit prices from different vendors for this product. The most recent price will be used.</p>

        <div class="divide-y divide-white/10">
            @foreach ($rows as $index => $row)
                <div class="py-4 first:pt-0">
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Vendor</label>
                            <select wire:model="rows.{{ $index }}.vendor_id" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm">
                                <option value="">Select a vendor…</option>
                                @foreach ($vendors as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                            </select>
                            @error("rows.{$index}.vendor_id") <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="w-48">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Vendor Price (Cost)</label>
                            <input type="number" step="0.01" min="0" wire:model="rows.{{ $index }}.price" placeholder="0.00" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm" />
                            @error("rows.{$index}.price") <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <button type="button" wire:click="toggleHistory({{ $row['vendor_id'] ?? 'null' }})"
                            @if(! $row['vendor_id']) disabled @endif
                            class="h-9 w-9 shrink-0 flex items-center justify-center rounded-lg border border-white/10 text-zinc-300 hover:bg-zinc-700 disabled:opacity-40 disabled:cursor-not-allowed"
                            title="Price history">
                            <x-heroicon-o-clock class="w-4 h-4" />
                        </button>
                        <button type="button" wire:click="removeRow({{ $index }})" wire:confirm="Remove this vendor's pricing for this product?"
                            class="h-9 w-9 shrink-0 flex items-center justify-center rounded-lg bg-red-600 hover:bg-red-500 text-white"
                            title="Delete">
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                    </div>

                    @if ($row['vendor_id'] && $historyVendorId === $row['vendor_id'])
                        <div class="mt-3 rounded-lg bg-zinc-900 border border-white/10 p-3">
                            <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-2">Price History</p>
                            @forelse ($this->history as $entry)
                                <div class="flex items-center justify-between text-xs py-1 {{ !$loop->last ? 'border-b border-white/5' : '' }}">
                                    <span class="text-zinc-400">{{ $entry->created_at->format('d M Y, g:i A') }} · {{ $entry->addedBy?->name ?? 'Unknown' }}</span>
                                    <span class="text-white font-medium">MVR {{ number_format($entry->price, 2) }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-zinc-500">No price history yet.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <button type="button" wire:click="addRow" class="mt-4 flex items-center gap-2 px-4 py-2 rounded-lg border border-white/10 text-zinc-200 text-sm font-medium hover:bg-zinc-700">
            <x-heroicon-o-plus-circle class="w-4 h-4" /> Add Vendor Price
        </button>
    </div>

    <div class="flex justify-end gap-2 mt-6">
        <button type="button" wire:click="save" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Update Prices</button>
        <a href="{{ route('crm.products') }}" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm font-medium hover:bg-zinc-700">Cancel</a>
    </div>
</div>
