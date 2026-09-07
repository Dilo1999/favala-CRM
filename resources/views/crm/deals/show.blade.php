<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.deals') }}" class="text-zinc-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
            <h1 class="text-2xl font-bold text-white">{{ $record->friendly_id }}</h1>
            @php($outcome = $record->outcome_status)
            <x-badge :color="match($outcome) { 'Converted' => 'green', 'Expired' => 'red', default => 'orange' }">{{ $outcome }}</x-badge>
            <x-badge :color="match($record->stage) { 'won' => 'green', 'lost' => 'red', 'hot' => 'orange', default => 'gray' }">{{ $record->stage_label }}</x-badge>
        </div>
        <div class="flex gap-2">
            @if (! $record->isConverted() && $record->quotations->isEmpty())
                <button wire:click="convertToQuotation" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-500 text-white text-sm font-semibold">
                    Convert to Quotation
                </button>
            @endif
            <a href="{{ route('crm.deals.edit', $record) }}" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-zinc-500">Customer</dt><dd class="text-white">{{ $record->customer?->company_name }}</dd></div>
                    <div><dt class="text-zinc-500">Deal Date</dt><dd class="text-white">{{ $record->deal_date->format('d M Y') }}</dd></div>
                    <div><dt class="text-zinc-500">Request Source</dt><dd class="text-white">{{ $record->request_source ?? '—' }}</dd></div>
                    <div><dt class="text-zinc-500">Assigned Staff</dt><dd class="text-white">{{ $record->assignedStaff?->name ?? 'Unassigned' }}</dd></div>
                    <div><dt class="text-zinc-500">Stage</dt><dd class="text-white">{{ $record->stage_label }}</dd></div>
                    <div><dt class="text-zinc-500">Expires</dt><dd class="text-white">{{ optional($record->expires_at)->format('d M Y H:i') }}</dd></div>
                </dl>
                @if ($record->additional_details)
                    <p class="text-sm text-zinc-300 mt-4 border-t border-white/10 pt-4">{{ $record->additional_details }}</p>
                @endif
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h3 class="font-bold text-white mb-3">Products Requested</h3>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-zinc-500 text-xs uppercase"><th class="pb-2">Product</th><th class="pb-2 text-right">Qty</th></tr></thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($record->products as $line)
                            <tr><td class="py-2 text-white">{{ $line->product?->description }}</td><td class="py-2 text-right text-zinc-300">{{ $line->qty }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @livewire('record-notes-panel', ['notableType' => \App\Models\Deal::class, 'notableId' => $record->id])
        </div>

        <div>
            @if ($record->quotations->isNotEmpty())
                <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                    <h3 class="font-bold text-white mb-3">Quotations</h3>
                    @foreach ($record->quotations as $q)
                        <a href="{{ route('crm.quotations.show', $q) }}" class="block text-sm text-accent hover:underline mb-1">{{ $q->friendly_id }} — MVR {{ number_format($q->grand_total, 2) }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
