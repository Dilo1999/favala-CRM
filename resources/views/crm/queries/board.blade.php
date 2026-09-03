<div>
    <h1 class="text-2xl font-bold text-white mb-6">Query Logger</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['label' => 'Total', 'value' => $this->kpis['total']],
            ['label' => 'New', 'value' => $this->kpis['new']],
            ['label' => 'Negotiating', 'value' => $this->kpis['negotiating']],
            ['label' => 'Completed', 'value' => $this->kpis['completed']],
        ] as $kpi)
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-4">
                <p class="text-xs text-zinc-500">{{ $kpi['label'] }}</p>
                <p class="text-2xl font-bold text-white">{{ $kpi['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="relative flex-1 min-w-[200px]">
            <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.debounce.400ms="search" placeholder="Search queries…" class="w-full pl-9 rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
        </div>
        <div class="flex rounded-lg border border-white/10 overflow-hidden text-sm">
            @foreach (['all' => 'All Time', 'today' => 'Today', 'week' => 'This Week', 'month' => 'This Month'] as $value => $label)
                <button wire:click="$set('timeFilter', '{{ $value }}')" class="px-3 py-2 {{ $timeFilter === $value ? 'bg-accent text-white' : 'text-zinc-400' }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ([
            \App\Models\SalesQuery::STATUS_NEW => 'New',
            \App\Models\SalesQuery::STATUS_NEGOTIATING => 'Negotiating',
            \App\Models\SalesQuery::STATUS_COMPLETED => 'Completed',
            \App\Models\SalesQuery::STATUS_DEAD => 'Dead',
        ] as $statusKey => $statusLabel)
            <div class="rounded-xl bg-zinc-900 border border-white/10 flex flex-col">
                <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                    <h3 class="font-bold text-sm text-white">{{ $statusLabel }}</h3>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-zinc-700 text-zinc-300">{{ $this->columns[$statusKey]->count() }}</span>
                </div>

                @if ($statusKey === \App\Models\SalesQuery::STATUS_NEW)
                    <div class="px-4 pt-3">
                        <button wire:click="$toggle('showNewQueryForm')" class="w-full text-sm font-medium text-accent border border-dashed border-accent/50 rounded-lg py-2">
                            + Log New Query
                        </button>
                        @if ($showNewQueryForm)
                            <div class="mt-3 space-y-2 bg-zinc-800 border border-white/10 rounded-lg p-3">
                                <select wire:model="newQuery.customer_id" class="w-full text-sm rounded-lg bg-zinc-700 border-white/10 text-white">
                                    <option value="">Select customer…</option>
                                    @foreach ($this->customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                                </select>
                                <textarea wire:model="newQuery.description" placeholder="Description" rows="2" class="w-full text-sm rounded-lg bg-zinc-700 border-white/10 text-white"></textarea>
                                <button wire:click="createQuery" class="w-full text-sm font-medium bg-accent text-white rounded-lg py-1.5">Save Query</button>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="p-3 space-y-3 flex-1 overflow-y-auto max-h-[70vh]">
                    @forelse ($this->columns[$statusKey] as $query)
                        <div class="bg-zinc-800 border border-white/10 rounded-lg p-3">
                            <div class="flex items-start justify-between">
                                <a href="{{ route('crm.queries.show', $query) }}" class="flex items-center gap-2 text-sm font-semibold text-white hover:text-accent">
                                    <x-heroicon-o-chat-alt class="w-4 h-4 text-zinc-500" />
                                    {{ $query->customer?->company_name }}
                                </a>
                                <select wire:change="moveTo({{ $query->id }}, $event.target.value)" class="text-xs rounded-md bg-zinc-700 border-white/10 text-white">
                                    @foreach (['new' => 'New', 'negotiating' => 'Negotiating', 'completed' => 'Completed', 'dead' => 'Dead'] as $val => $label)
                                        <option value="{{ $val }}" @selected($query->status === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="text-xs text-zinc-500 mt-1">{{ $query->customer?->phone }} · {{ $query->friendly_id }}</p>
                            @if ($query->description)
                                <p class="text-xs text-zinc-400 mt-2">{{ \Illuminate\Support\Str::limit($query->description, 90) }}</p>
                            @endif
                            @if (!empty($query->tags))
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach ($query->tags as $tag)
                                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-accent/10 text-accent">{{ $tag }}</span>
                                    @endforeach
                                    @if ($query->follow_up)
                                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-yellow-500/10 text-yellow-500">Follow-up</span>
                                    @endif
                                </div>
                            @endif
                            <div class="flex items-center justify-between mt-3 text-xs text-zinc-500">
                                <span>{{ $query->quotation?->friendly_id ?? '—' }}@if($query->value) · MVR {{ number_format($query->value, 2) }} @endif</span>
                                <span>{{ $query->assignedStaff?->name ?? 'Unassigned' }} · {{ $query->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-500 text-center py-6">No queries in this stage.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
