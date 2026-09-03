<div>
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.deliveries') }}" class="text-gray-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
            <h1 class="text-2xl font-bold text-white">{{ $record->friendly_id }}</h1>
            <x-badge :color="$record->status === 'completed' ? 'green' : 'orange'">{{ ucfirst($record->status) }}</x-badge>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('print.delivery', $record) }}" target="_blank" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Print</a>
            @if ($record->status === 'pending')
                <button wire:click="markComplete" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold">Mark as Complete</button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Customer</dt><dd class="text-white">{{ $record->customer?->company_name }}</dd></div>
                    <div><dt class="text-gray-500">Invoice</dt><dd class="text-white"><a href="{{ route('crm.invoices.show', $record->invoice) }}" class="text-orange-400 hover:underline">{{ $record->invoice?->friendly_id }}</a></dd></div>
                    <div><dt class="text-gray-500">Contact</dt><dd class="text-white">{{ $record->contact_name }} — {{ $record->contact_phone }}</dd></div>
                    <div><dt class="text-gray-500">Location</dt><dd class="text-white">{{ $record->location }}</dd></div>
                    <div><dt class="text-gray-500">Deadline</dt><dd class="text-white">{{ optional($record->deadline_date)->format('d M Y') }} {{ $record->deadline_time }}</dd></div>
                    <div><dt class="text-gray-500">Time Left</dt><dd class="{{ $record->isOverdue() ? 'text-red-400' : 'text-white' }}">{{ $record->time_left }}</dd></div>
                </dl>
            </div>

            <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                <h3 class="font-bold text-white mb-3">Items</h3>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 text-xs uppercase"><th class="pb-2">Product</th><th class="pb-2 text-right">Balance Qty</th><th class="pb-2 text-right">Delivery Qty</th></tr></thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach ($record->items as $item)
                            <tr>
                                <td class="py-2 text-white">{{ $item->product?->description }}</td>
                                <td class="py-2 text-right text-gray-400">{{ $item->balance_qty }}</td>
                                <td class="py-2 text-right text-white">{{ $item->delivery_qty }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @livewire('record-notes-panel', ['notableType' => \App\Models\Delivery::class, 'notableId' => $record->id])
        </div>
    </div>
</div>
