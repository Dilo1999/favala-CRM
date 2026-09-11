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
                        <button wire:click="$set('showNewQueryForm', true)" class="w-full text-sm font-medium text-accent-light border border-dashed border-accent-light/50 rounded-lg py-2 hover:bg-accent/10">
                            + Log New Query
                        </button>
                    </div>
                @endif

                <div class="p-3 space-y-3 flex-1 overflow-y-auto max-h-[70vh]">
                    @forelse ($this->columns[$statusKey] as $query)
                        <div class="bg-zinc-800 border border-white/10 rounded-lg p-3">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('crm.queries.show', $query) }}" class="flex items-center gap-2 text-sm font-semibold text-white hover:text-accent-light min-w-0">
                                    <x-heroicon-o-chat-alt class="w-4 h-4 text-zinc-500 shrink-0" />
                                    <span class="truncate">{{ $query->customer?->company_name }}</span>
                                </a>
                                <div class="flex items-center gap-1 shrink-0">
                                    <select wire:change="moveTo({{ $query->id }}, $event.target.value)" class="text-xs rounded-md bg-zinc-700 border-white/10 text-white">
                                        @foreach (['new' => 'New', 'negotiating' => 'Negotiating', 'completed' => 'Completed', 'dead' => 'Dead'] as $val => $label)
                                            <option value="{{ $val }}" @selected($query->status === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <x-row-menu>
                                        <a href="{{ route('crm.queries.show', $query) }}" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</a>
                                        <button wire:click="delete({{ $query->id }})" wire:confirm="Delete this query? This cannot be undone." class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                                    </x-row-menu>
                                </div>
                            </div>
                            <p class="text-xs text-zinc-500 mt-1">{{ $query->customer?->phone }} · {{ $query->friendly_id }}</p>
                            @if ($query->description)
                                <p class="text-xs text-zinc-400 mt-2">{{ \Illuminate\Support\Str::limit($query->description, 90) }}</p>
                            @endif
                            @if (!empty($query->tags))
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach ($query->tags as $tag)
                                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-accent/10 text-accent-light">{{ $tag }}</span>
                                    @endforeach
                                    @if ($query->follow_up)
                                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-yellow-500/10 text-yellow-500">Follow-up</span>
                                    @endif
                                </div>
                            @endif
                            <div class="mt-3 pt-2 border-t border-white/5 text-xs text-zinc-500 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span>{{ $query->quotation ? 'Quotation # '.$query->quotation->friendly_id : '—' }}</span>
                                    @if ($query->value)
                                        <span class="text-zinc-300 font-medium">Value: MVR {{ number_format($query->value, 2) }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>{{ $query->assignedStaff?->name ?? 'Unassigned' }}</span>
                                    <span>{{ $query->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-500 text-center py-6">No queries in this stage.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- Log New Query modal --}}
    @if ($showNewQueryForm)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="closeNewQueryForm">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-lg p-6">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-white">Log New Customer Inquiry</h2>
                        <p class="text-sm text-zinc-500 mt-1">Record the initial contact from a potential customer.</p>
                    </div>
                    <button wire:click="closeNewQueryForm" class="text-zinc-500 hover:text-white">
                        <x-heroicon-o-x class="w-5 h-5" />
                    </button>
                </div>

                <form wire:submit.prevent="createQuery" class="grid grid-cols-2 gap-x-4 gap-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-200 mb-2">Source</label>
                        <select wire:model="newQuery.query_source" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5">
                            <option value="">Select a source…</option>
                            @foreach ($this->querySources as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-200 mb-2">Assign To</label>
                        <select wire:model="newQuery.assigned_staff_id" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5">
                            <option value="">Unassigned</option>
                            @foreach ($this->staff as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-200 mb-2">Customer Name</label>
                        <input type="text" wire:model="newQuery.customer_name" placeholder="e.g., Ali"
                            class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5 placeholder-zinc-600" />
                        @error('newQuery.customer_name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-200 mb-2">Customer Phone</label>
                        <input type="text" wire:model="newQuery.customer_phone" placeholder="e.g., 7771234"
                            class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5 placeholder-zinc-600" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-200 mb-2">Query Type</label>
                        <select wire:model="newQuery.query_type" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5">
                            <option value="">Select a type…</option>
                            @foreach ($this->queryTypes as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-200 mb-2">Product Category</label>
                        <select wire:model="newQuery.product_category" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5">
                            <option value="">Select a category…</option>
                            @foreach ($this->productCategories as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-zinc-200 mb-2">Query Details / Product List</label>
                        <textarea wire:model="newQuery.description" rows="4"
                            placeholder="Enter all relevant details about the customer's inquiry, or paste a list of required products here…"
                            class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm px-4 py-2.5 placeholder-zinc-600 resize-none"></textarea>
                    </div>

                    <div class="col-span-2 flex justify-end gap-2 mt-2">
                        <button type="button" wire:click="closeNewQueryForm" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm font-medium hover:bg-zinc-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Log Query</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
