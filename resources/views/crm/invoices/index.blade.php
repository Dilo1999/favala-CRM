<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Invoices</h1>
        <button wire:click="openCreateModal" class="flex items-center gap-1 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
            <x-heroicon-o-plus class="w-4 h-4" /> New Manual Invoice
        </button>
    </div>

    <div class="relative mb-4 max-w-md">
        <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by invoice ID or customer…" class="w-full pl-9 rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
    </div>

    <div class="rounded-xl border border-white/10 bg-zinc-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                    <th class="p-3">Invoice ID</th><th class="p-3">Customer</th><th class="p-3">Last Payment</th>
                    <th class="p-3">Payment Status</th><th class="p-3">Amount</th><th class="p-3">Balance Due</th><th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse ($invoices as $invoice)
                    @php($last = $invoice->payments->sortByDesc('date')->first())
                    <tr class="hover:bg-zinc-700/40">
                        <td class="p-3 text-white font-medium">{{ $invoice->friendly_id }}</td>
                        <td class="p-3 text-zinc-300">{{ $invoice->customer?->company_name }}</td>
                        <td class="p-3 text-zinc-400">{{ $last ? $last->date->format('d M Y') : 'N/A' }}</td>
                        <td class="p-3">
                            <x-badge :color="$invoice->payment_status === 'paid' ? 'green' : ($invoice->payment_status === 'partial' ? 'orange' : 'gray')">
                                {{ ucfirst($invoice->payment_status) }}
                            </x-badge>
                        </td>
                        <td class="p-3 text-white">MVR {{ number_format($invoice->grand_total, 2) }}</td>
                        <td class="p-3 text-zinc-300">MVR {{ number_format($invoice->balance_due, 2) }}</td>
                        <td class="p-3 text-right">
                            <x-row-menu>
                                <a href="{{ route('crm.invoices.show', $invoice) }}" class="block px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">View Details</a>
                                <a href="{{ route('print.invoice', $invoice) }}" target="_blank" class="block px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Print</a>
                                <button wire:click="delete({{ $invoice->id }})" wire:confirm="Delete this invoice?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-6 text-center text-zinc-500">No invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $invoices->links() }}</div>

    @if ($showCreateModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showCreateModal', false)">
            <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-md p-6">
                <div class="flex items-start justify-between mb-1">
                    <h2 class="text-lg font-bold text-white">New Manual Invoice</h2>
                    <button type="button" wire:click="$set('showCreateModal', false)" class="text-zinc-500 hover:text-white -mt-1 -mr-1 p-1">
                        <x-heroicon-o-x class="w-5 h-5" />
                    </button>
                </div>
                <p class="text-xs text-zinc-500 mb-4">Log an external sale by providing the total amount and a reference.</p>

                <form wire:submit.prevent="save" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Customer <span class="text-red-400">*</span></label>
                        <select wire:model="form.customer_id" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                            <option value="">Select a customer…</option>
                            @foreach ($customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                        </select>
                        @error('form.customer_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Total Amount (inc. GST) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" wire:model="form.total_amount" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                        @error('form.total_amount') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Reference Number</label>
                        <input type="text" wire:model="form.reference_number" placeholder="e.g. old invoice #, bank transfer ref…" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Sales Staff</label>
                            <select wire:model="form.staff_id" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                                <option value="">Unassigned</option>
                                @foreach ($staff as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1.5">Invoice Date</label>
                            <div class="relative" x-data="datePicker('form.invoice_date', '{{ $form['invoice_date'] }}')">
                                <x-heroicon-o-calendar class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                                <input type="text" x-ref="input" readonly class="w-full pl-9 rounded-lg bg-zinc-700 border-white/10 text-white text-sm cursor-pointer" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 rounded-lg text-zinc-400 hover:text-white text-sm font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Create Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
