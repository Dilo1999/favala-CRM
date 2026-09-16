<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('crm.invoices.show', $invoice) }}" class="text-zinc-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
        <h1 class="text-2xl font-bold text-white">Create Delivery Note for {{ $invoice->friendly_id }}</h1>
    </div>

    <form wire:submit.prevent="save" class="max-w-2xl space-y-6">
        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-xs text-zinc-400 mb-1">Delivery Location</label>
                <input type="text" wire:model="location" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                @error('location') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Contact Person at Location</label>
                <input type="text" wire:model="contact_name" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Contact Phone</label>
                <input type="text" wire:model="contact_phone" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Delivery Deadline (Date)</label>
                <input type="date" wire:model="deadline_date" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Time (optional)</label>
                <input type="time" wire:model="deadline_time" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            </div>
        </div>

        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
            <h2 class="font-bold text-white mb-4">Items to Deliver</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-zinc-500 text-xs uppercase"><th class="pb-2">Product</th><th class="pb-2 text-right">Balance Qty</th><th class="pb-2 text-right">Delivery Qty</th></tr></thead>
                <tbody class="divide-y divide-white/10">
                    @foreach ($lines as $i => $line)
                        <tr>
                            <td class="py-2 text-white">{{ $line['label'] }}</td>
                            <td class="py-2 text-right text-zinc-400">{{ $line['balance_qty'] }}</td>
                            <td class="py-2 text-right"><input type="number" step="1" min="0" wire:model="lines.{{ $i }}.delivery_qty" class="w-24 rounded-lg bg-zinc-700 border-white/10 text-white text-sm text-right" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('crm.invoices.show', $invoice) }}" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Create Delivery Note</button>
        </div>
    </form>
</div>
