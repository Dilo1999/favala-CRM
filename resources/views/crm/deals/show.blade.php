<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.deals') }}" class="text-gray-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
            <h1 class="text-2xl font-bold text-white">{{ $record->friendly_id }}</h1>
            @php($outcome = $record->outcome_status)
            <x-badge :color="match($outcome) { 'Converted' => 'green', 'Expired', 'Lost' => 'red', default => 'orange' }">{{ $outcome }}</x-badge>
        </div>
        <div class="flex gap-2">
            @if (! $record->isConverted() && $record->quotations->isEmpty())
                <button wire:click="convertToQuotation" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold">
                    Convert to Quotation
                </button>
            @endif
            <a href="{{ route('crm.deals.edit', $record) }}" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Customer</dt><dd class="text-white">{{ $record->customer?->company_name }}</dd></div>
                    <div><dt class="text-gray-500">Deal Date</dt><dd class="text-white">{{ $record->deal_date->format('d M Y') }}</dd></div>
                    <div><dt class="text-gray-500">Request Source</dt><dd class="text-white">{{ $record->request_source ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Assigned Staff</dt><dd class="text-white">{{ $record->assignedStaff?->name ?? 'Unassigned' }}</dd></div>
                    <div><dt class="text-gray-500">Stage</dt><dd class="text-white">{{ $record->stage === 'hot' ? '🔥 Hot Deal' : ucfirst($record->stage) }}</dd></div>
                    <div><dt class="text-gray-500">Expires</dt><dd class="text-white">{{ optional($record->expires_at)->format('d M Y H:i') }}</dd></div>
                </dl>
                @if ($record->additional_details)
                    <p class="text-sm text-gray-300 mt-4 border-t border-gray-800 pt-4">{{ $record->additional_details }}</p>
                @endif
            </div>

            <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                <h3 class="font-bold text-white mb-3">Products Requested</h3>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 text-xs uppercase"><th class="pb-2">Product</th><th class="pb-2 text-right">Qty</th></tr></thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach ($record->products as $line)
                            <tr><td class="py-2 text-white">{{ $line->product?->description }}</td><td class="py-2 text-right text-gray-300">{{ $line->qty }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @livewire('record-notes-panel', ['notableType' => \App\Models\Deal::class, 'notableId' => $record->id])
        </div>

        <div>
            @if ($record->quotations->isNotEmpty())
                <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                    <h3 class="font-bold text-white mb-3">Quotations</h3>
                    @foreach ($record->quotations as $q)
                        <a href="{{ route('crm.quotations.show', $q) }}" class="block text-sm text-orange-400 hover:underline mb-1">{{ $q->friendly_id }} — MVR {{ number_format($q->grand_total, 2) }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
