<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('crm.queries') }}" class="text-gray-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
        <h1 class="text-2xl font-bold text-white">Query {{ $record->friendly_id }}</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-white">{{ $record->customer?->company_name }}</h2>
                        <p class="text-sm text-gray-500">{{ $record->customer?->phone }}</p>
                    </div>
                    @if ($record->quotation)
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-500/10 text-emerald-400">{{ $record->quotation->friendly_id }}</span>
                    @endif
                </div>

                @if ($record->description)
                    <p class="text-sm text-gray-300 mt-4">{{ $record->description }}</p>
                @endif

                @if (!empty($record->tags))
                    <div class="flex flex-wrap gap-1 mt-3">
                        @foreach ($record->tags as $tag)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-orange-600/10 text-orange-400">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                <dl class="grid grid-cols-2 gap-4 mt-5 text-sm">
                    <div><dt class="text-gray-500">Assigned Staff</dt><dd class="text-white">{{ $record->assignedStaff?->name ?? 'Unassigned' }}</dd></div>
                    <div><dt class="text-gray-500">Value</dt><dd class="text-white">{{ $record->value ? 'MVR '.number_format($record->value, 2) : '—' }}</dd></div>
                    <div><dt class="text-gray-500">Logged</dt><dd class="text-white">{{ $record->created_at->format('d M Y H:i') }}</dd></div>
                    <div><dt class="text-gray-500">Source</dt><dd class="text-white">{{ ucfirst($record->source) }}</dd></div>
                </dl>
            </div>

            @livewire('record-notes-panel', ['notableType' => \App\Models\SalesQuery::class, 'notableId' => $record->id])
        </div>

        <div>
            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                <h3 class="text-sm font-bold text-white mb-3">Status</h3>
                <label class="block text-xs font-medium text-gray-400 mb-1">Query Status</label>
                <select wire:model="status" class="w-full text-sm rounded-lg bg-gray-800 border-gray-700 text-white mb-4">
                    @foreach (\App\Models\SalesQuery::STATUSES as $val => $label) <option value="{{ $val }}">{{ $label }}</option> @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-gray-300 mb-4">
                    <input type="checkbox" wire:model="followUp" class="rounded border-gray-700 bg-gray-800 text-orange-600" />
                    Mark for Follow-up
                </label>
                <button wire:click="save" class="w-full bg-orange-600 hover:bg-orange-500 text-white text-sm font-medium rounded-lg py-2">Save Changes</button>
            </div>
        </div>
    </div>
</div>
