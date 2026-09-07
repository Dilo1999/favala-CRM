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

    <div class="rounded-xl border border-white/10 bg-zinc-800 p-5">
        <h2 class="text-lg font-bold text-white">Deal List</h2>
        <p class="text-sm text-zinc-500 mt-1 mb-5">Manage all incoming customer requests for quotations. Click a row to open chat.</p>

        @if ($viewMode === 'grid')
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                @forelse ($deals as $deal)
                    @php
                        $outcome = $deal->outcome_status;
                        $outcomeColor = match ($outcome) { 'Converted' => 'green', 'Expired' => 'red', default => 'orange' };
                        $stageColor = match ($deal->stage) { 'won' => 'green', 'lost' => 'red', 'hot' => 'orange', default => 'gray' };
                        $borderColor = match ($outcome) { 'Converted' => 'border-l-green-500', 'Expired' => 'border-l-red-500', default => 'border-l-white/10' };
                    @endphp
                    <div wire:click="view({{ $deal->id }})" class="cursor-pointer rounded-xl bg-zinc-900 border border-white/10 {{ $borderColor }} border-l-4 p-4 hover:border-accent transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-bold text-white">{{ $deal->friendly_id }}</span>
                            <div wire:click.stop>
                                <x-row-menu>
                                    <button wire:click="view({{ $deal->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">View</button>
                                    <button wire:click="edit({{ $deal->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                                    <button wire:click="deleteDeal({{ $deal->id }})" wire:confirm="Delete this deal? This cannot be undone." class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                                </x-row-menu>
                            </div>
                        </div>
                        <p class="text-sm text-zinc-300 truncate">{{ $deal->customer?->company_name }}</p>
                        <p class="text-xs text-zinc-500 mt-1">{{ $deal->created_at->format('d/m/Y g:i A') }}</p>
                        <p class="text-xs text-zinc-500">{{ $deal->aging_line }}</p>
                        <div class="flex flex-wrap gap-1 mt-2">
                            <x-badge :color="$outcomeColor">{{ $outcome }}</x-badge>
                            <x-badge :color="$stageColor">{{ $deal->stage_label }}</x-badge>
                        </div>
                        <p class="text-xs text-zinc-500 mt-3 border-t border-white/10 pt-2">Assigned to: {{ $deal->assignedStaff?->name ?? 'Unassigned' }}</p>
                    </div>
                @empty
                    <p class="text-zinc-500 col-span-full text-center py-8">No deals found.</p>
                @endforelse
            </div>
        @else
            <div class="overflow-x-auto -mx-5">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                            <th class="px-5 py-3">Deal ID</th><th class="px-5 py-3">Customer</th><th class="px-5 py-3">Created</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Stage</th><th class="px-5 py-3">Assigned to</th><th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($deals as $deal)
                            @php
                                $outcome = $deal->outcome_status;
                                $outcomeColor = match ($outcome) { 'Converted' => 'green', 'Expired' => 'red', default => 'orange' };
                                $stageColor = match ($deal->stage) { 'won' => 'green', 'lost' => 'red', 'hot' => 'orange', default => 'gray' };
                            @endphp
                            <tr class="hover:bg-zinc-700/40 cursor-pointer" wire:click="view({{ $deal->id }})">
                                <td class="px-5 py-3 text-white font-medium">{{ $deal->friendly_id }}</td>
                                <td class="px-5 py-3 text-zinc-300">{{ $deal->customer?->company_name }}</td>
                                <td class="px-5 py-3 text-zinc-400">{{ $deal->created_at->diffForHumans() }}</td>
                                <td class="px-5 py-3"><x-badge :color="$outcomeColor">{{ $outcome }}</x-badge></td>
                                <td class="px-5 py-3"><x-badge :color="$stageColor">{{ $deal->stage_label }}</x-badge></td>
                                <td class="px-5 py-3 text-zinc-400">{{ $deal->assignedStaff?->name ?? 'Unassigned' }}</td>
                                <td class="px-5 py-3 text-right" wire:click.stop>
                                    <x-row-menu>
                                        <button wire:click="view({{ $deal->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">View</button>
                                        <button wire:click="edit({{ $deal->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                                        <button wire:click="deleteDeal({{ $deal->id }})" wire:confirm="Delete this deal? This cannot be undone." class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                                    </x-row-menu>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-6 text-center text-zinc-500">No deals found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">{{ $deals->links() }}</div>

    {{-- View Deal modal --}}
    @if ($viewingId && $this->viewingDeal)
        @php($record = $this->viewingDeal)
        @php($outcome = $record->outcome_status)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="closeView">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-white">{{ $record->friendly_id }}</h2>
                        <x-badge :color="match($outcome) { 'Converted' => 'green', 'Expired' => 'red', default => 'orange' }">{{ $outcome }}</x-badge>
                        <x-badge :color="match($record->stage) { 'won' => 'green', 'lost' => 'red', 'hot' => 'orange', default => 'gray' }">{{ $record->stage_label }}</x-badge>
                    </div>
                    <div class="flex items-center gap-2">
                        @if (! $record->isConverted() && $record->quotations->isEmpty())
                            <button wire:click="convertToQuotation({{ $record->id }})" class="px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-500 text-white text-sm font-semibold">
                                Convert to Quotation
                            </button>
                        @endif
                        <button wire:click="edit({{ $record->id }})" class="px-3 py-1.5 rounded-lg border border-white/10 text-zinc-300 text-sm hover:bg-zinc-700">Edit</button>
                        <button wire:click="closeView" class="text-zinc-500 hover:text-white p-1.5">
                            <x-heroicon-o-x class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="rounded-xl bg-zinc-900 border border-white/10 p-5">
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

                        <div class="rounded-xl bg-zinc-900 border border-white/10 p-5">
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

                        <div wire:key="deal-notes-{{ $record->id }}">
                            @livewire('record-notes-panel', ['notableType' => \App\Models\Deal::class, 'notableId' => $record->id], key('deal-notes-'.$record->id))
                        </div>
                    </div>

                    <div>
                        @if ($record->quotations->isNotEmpty())
                            <div class="rounded-xl bg-zinc-900 border border-white/10 p-5">
                                <h3 class="font-bold text-white mb-3">Quotations</h3>
                                @foreach ($record->quotations as $q)
                                    <a href="{{ route('crm.quotations.show', $q) }}" class="block text-sm text-accent hover:underline mb-1">{{ $q->friendly_id }} — MVR {{ number_format($q->grand_total, 2) }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Deal modal --}}
    @if ($editingId)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="closeEdit">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-white">Edit Deal</h2>
                    <button wire:click="closeEdit" class="text-zinc-500 hover:text-white p-1.5">
                        <x-heroicon-o-x class="w-5 h-5" />
                    </button>
                </div>

                <form wire:submit.prevent="saveEdit" class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Customer</label>
                            <select wire:model="editForm.customer_id" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm">
                                @foreach ($customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                            </select>
                            @error('editForm.customer_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Deal Date</label>
                            <input type="date" wire:model="editForm.deal_date" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Request Source</label>
                            <select wire:model="editForm.request_source" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm">
                                <option value="">Select…</option>
                                @foreach ($requestSources as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Assigned Staff</label>
                            <select wire:model="editForm.assigned_staff_id" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm">
                                <option value="">Unassigned</option>
                                @foreach ($staff as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Deal Stage</label>
                            @if ($editForm['stage'] === 'won')
                                <input type="text" value="Won (automatic)" disabled class="w-full rounded-lg bg-zinc-900 border-white/10 text-zinc-500 text-sm" />
                            @else
                                <select wire:model="editForm.stage" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm">
                                    <option value="potential">Potential</option>
                                    <option value="hot">🔥 Hot Deal</option>
                                    <option value="lost">Lost</option>
                                </select>
                            @endif
                        </div>
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Additional Details</label>
                            <textarea wire:model="editForm.additional_details" rows="3" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-2 border-t border-white/10">
                        <button type="button" wire:click="deleteDeal({{ $editingId }})" wire:confirm="Delete this deal? This cannot be undone."
                            class="flex items-center gap-2 px-4 py-2 rounded-lg border border-red-800 text-red-400 text-sm font-medium hover:bg-red-500/10 transition-colors">
                            <x-heroicon-o-trash class="w-4 h-4" /> Delete Deal
                        </button>
                        <div class="flex gap-2">
                            <button type="button" wire:click="closeEdit" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm font-medium hover:bg-zinc-700 transition-colors">Cancel</button>
                            <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
                                <x-heroicon-o-check class="w-4 h-4" /> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
