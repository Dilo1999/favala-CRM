<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Deals</h1>
        <div class="flex items-center gap-3">
            <div class="flex rounded-lg border border-white/10 overflow-hidden text-sm">
                <button wire:click="$set('viewMode', 'list')" class="px-3 py-1.5 {{ $viewMode === 'list' ? 'bg-accent text-white' : 'text-zinc-400' }}">
                    <x-heroicon-o-view-list class="w-4 h-4" />
                </button>
                <button wire:click="$set('viewMode', 'grid')" class="px-3 py-1.5 {{ $viewMode === 'grid' ? 'bg-accent text-white' : 'text-zinc-400' }}">
                    <x-heroicon-o-view-grid class="w-4 h-4" />
                </button>
            </div>
            <a href="{{ route('crm.deals.create') }}" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
                <x-heroicon-o-plus class="w-4 h-4" /> New Deal
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        @foreach ([
            ['label' => 'In Progress', 'value' => $this->kpis['in_progress']],
            ['label' => 'Potential', 'value' => $this->kpis['potential']],
            ['label' => 'Hot Deals', 'value' => $this->kpis['hot']],
            ['label' => 'Assigned to Me', 'value' => $this->kpis['mine']],
            ['label' => 'Converted', 'value' => $this->kpis['converted']],
        ] as $kpi)
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-4">
                <p class="text-xs text-zinc-500">{{ $kpi['label'] }}</p>
                <p class="text-xl font-bold text-white">{{ $kpi['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex rounded-lg border border-white/10 overflow-hidden text-sm">
            @foreach (['all' => 'All Deals', 'in_progress' => 'In Progress', 'converted' => 'Converted', 'expired' => 'Expired'] as $val => $label)
                <button wire:click="$set('tab', '{{ $val }}')" class="px-3 py-2 {{ $tab === $val ? 'bg-accent text-white' : 'text-zinc-400' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="relative max-w-sm flex-1">
            <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.debounce.400ms="search" placeholder="Search by customer, deal id or staff…" class="w-full pl-9 rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
        </div>
    </div>

    @if ($viewMode === 'grid')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($deals as $deal)
                @php
                    $outcome = $deal->outcome_status;
                    $outcomeColor = match ($outcome) { 'Converted' => 'green', 'Expired', 'Lost' => 'red', default => 'orange' };
                @endphp
                <a href="{{ route('crm.deals.show', $deal) }}" class="block rounded-xl bg-zinc-800 border border-white/10 p-4 hover:border-accent">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold text-white">{{ $deal->friendly_id }}</span>
                        <div class="flex gap-1">
                            <x-badge :color="$outcomeColor">{{ $outcome }}</x-badge>
                        </div>
                    </div>
                    <p class="text-sm text-zinc-300">{{ $deal->customer?->company_name }}</p>
                    <p class="text-xs text-zinc-500 mt-1">{{ $deal->created_at->diffForHumans() }}</p>
                    <p class="text-xs text-zinc-500 mt-3 border-t border-white/10 pt-2">Assigned to: {{ $deal->assignedStaff?->name ?? 'Unassigned' }}</p>
                </a>
            @empty
                <p class="text-zinc-500 col-span-full text-center py-8">No deals found.</p>
            @endforelse
        </div>
    @else
        <div class="rounded-xl border border-white/10 bg-zinc-800 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                        <th class="p-3">Deal ID</th><th class="p-3">Customer</th><th class="p-3">Created</th><th class="p-3">Status</th><th class="p-3">Stage</th><th class="p-3">Assigned to</th><th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($deals as $deal)
                        @php
                            $outcome = $deal->outcome_status;
                            $outcomeColor = match ($outcome) { 'Converted' => 'green', 'Expired', 'Lost' => 'red', default => 'orange' };
                        @endphp
                        <tr class="hover:bg-zinc-700/40">
                            <td class="p-3 text-white font-medium">{{ $deal->friendly_id }}</td>
                            <td class="p-3 text-zinc-300">{{ $deal->customer?->company_name }}</td>
                            <td class="p-3 text-zinc-400">{{ $deal->created_at->diffForHumans() }}</td>
                            <td class="p-3"><x-badge :color="$outcomeColor">{{ $outcome }}</x-badge></td>
                            <td class="p-3 text-zinc-400">{{ $deal->stage === 'hot' ? '🔥 Hot Deal' : ucfirst($deal->stage) }}</td>
                            <td class="p-3 text-zinc-400">{{ $deal->assignedStaff?->name ?? 'Unassigned' }}</td>
                            <td class="p-3 text-right">
                                <x-row-menu>
                                    <a href="{{ route('crm.deals.show', $deal) }}" class="block px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">View</a>
                                    <a href="{{ route('crm.deals.edit', $deal) }}" class="block px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</a>
                                </x-row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-6 text-center text-zinc-500">No deals found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <div class="mt-4">{{ $deals->links() }}</div>
</div>
