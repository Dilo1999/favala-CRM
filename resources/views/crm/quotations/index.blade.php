<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Quotations</h1>
        <a href="{{ route('crm.quotations.create') }}" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
            <x-heroicon-o-plus class="w-4 h-4" /> New Quotation
        </a>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
        <div class="relative flex-1 min-w-[240px]">
            <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.debounce.400ms="search" placeholder="Search by ID, customer or staff…" class="w-full pl-9 rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
        </div>
        <select wire:model="statusFilter" class="rounded-lg bg-zinc-800 border-white/10 text-white text-sm">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option><option value="sent">Sent</option>
        </select>
    </div>

    <div class="rounded-xl border border-white/10 bg-zinc-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                    <th class="p-3">Quotation ID</th><th class="p-3">Deal Number</th><th class="p-3">Customer</th>
                    <th class="p-3">Date</th><th class="p-3">Staff</th><th class="p-3">Status</th><th class="p-3">Amount</th><th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse ($quotations as $q)
                    <tr onclick="window.location='{{ route('crm.quotations.show', $q) }}'" class="hover:bg-zinc-700/40 cursor-pointer">
                        <td class="p-3 text-white font-medium">{{ $q->friendly_id }}</td>
                        <td class="p-3 text-zinc-400">{{ $q->deal?->friendly_id ?? '—' }}</td>
                        <td class="p-3 text-zinc-300">{{ $q->customer?->company_name }}</td>
                        <td class="p-3 text-zinc-400">{{ $q->quotation_date->format('d M Y') }}</td>
                        <td class="p-3 text-zinc-400">{{ $q->staff?->name }}</td>
                        <td class="p-3"><x-badge :color="$q->status === 'sent' ? 'green' : 'gray'">{{ ucfirst($q->status) }}</x-badge></td>
                        <td class="p-3 text-white">MVR {{ number_format($q->grand_total, 2) }}</td>
                        <td class="p-3 text-right" @click.stop>
                            <x-row-menu>
                                <p class="px-3 pt-1 pb-1.5 text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Actions</p>
                                <a href="{{ route('crm.quotations.show', $q) }}" class="flex items-center gap-2 px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">
                                    <x-heroicon-o-eye class="w-4 h-4" /> View
                                </a>
                                <a href="{{ route('print.quotation', $q) }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">
                                    <x-heroicon-o-printer class="w-4 h-4" /> Print
                                </a>
                                <a href="{{ route('crm.quotations.edit', $q) }}" class="block px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</a>
                                @if ($q->status === 'draft')
                                    <button wire:click="markSent({{ $q->id }})" class="flex items-center gap-2 w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">
                                        <x-heroicon-o-paper-airplane class="w-4 h-4" /> Mark as Sent
                                    </button>
                                @endif
                                @if ($q->invoices()->count() === 0)
                                    <button wire:click="convert({{ $q->id }})" wire:confirm="Convert this quotation to an invoice?" class="flex items-center gap-2 w-full text-left px-3 py-1.5 text-green-400 hover:bg-zinc-700">
                                        <x-heroicon-o-currency-dollar class="w-4 h-4" /> Convert to Invoice
                                    </button>
                                @endif
                                <button wire:click="delete({{ $q->id }})" wire:confirm="Delete this quotation?" class="flex items-center gap-2 w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">
                                    <x-heroicon-o-trash class="w-4 h-4" /> Delete
                                </button>
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-6 text-center text-zinc-500">No quotations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $quotations->links() }}</div>
</div>
