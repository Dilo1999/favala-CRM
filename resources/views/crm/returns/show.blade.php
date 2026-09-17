<div>
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.returns') }}" class="text-zinc-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
            <h1 class="text-2xl font-bold text-white">{{ $record->friendly_id }}</h1>
            <x-badge :color="match($record->status) { 'refunded' => 'green', 'processed' => 'blue', default => 'orange' }">
                {{ \App\Models\SalesReturn::STATUSES[$record->status] }}
            </x-badge>
            @if ($record->status !== \App\Models\SalesReturn::STATUS_REFUNDED && $record->approval_status === 'pending')
                <x-badge color="gray">Awaiting Approval</x-badge>
            @elseif ($record->approval_status === 'rejected')
                <x-badge color="red">Rejected</x-badge>
            @endif
        </div>
        <div class="flex gap-2 flex-wrap">
            @if ($record->status === \App\Models\SalesReturn::STATUS_REFUNDED)
                <a href="{{ route('print.credit-note', $record) }}" target="_blank"
                   class="flex items-center gap-1.5 px-4 py-2 rounded-lg border border-white/10 text-zinc-200 text-sm font-medium hover:bg-zinc-700">
                    <x-heroicon-o-document-text class="w-4 h-4" /> View Credit Note
                </a>
            @endif
            @foreach (\App\Models\SalesReturn::STATUSES as $val => $label)
                @if ($val !== $record->status && $val !== \App\Models\SalesReturn::STATUS_REFUNDED && ($record->status !== \App\Models\SalesReturn::STATUS_REFUNDED || auth()->user()->canApproveReturns()))
                    <button wire:click="updateStatus('{{ $val }}')"
                            class="px-4 py-2 rounded-lg border border-white/10 text-zinc-200 text-sm font-medium hover:bg-zinc-700">
                        Mark {{ $label }}
                    </button>
                @endif
            @endforeach
            @if ($record->status !== \App\Models\SalesReturn::STATUS_REFUNDED && $record->approval_status === 'pending' && auth()->user()->canApproveReturns())
                <button wire:click="reject" class="px-4 py-2 rounded-lg border border-white/10 text-red-400 text-sm font-semibold hover:bg-zinc-700">Reject</button>
                <button wire:click="approve" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-green-600 hover:bg-green-500 text-white text-sm font-semibold">
                    <x-heroicon-o-check class="w-4 h-4" /> Approve &amp; Refund
                </button>
            @endif
        </div>
    </div>

    @if ($record->status !== \App\Models\SalesReturn::STATUS_REFUNDED && $record->approval_status === 'pending')
        <div class="rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-sm px-4 py-3 mb-6">
            @if (auth()->user()->canApproveReturns())
                This return is awaiting your review. Approve to process the refund, or reject to decline it.
            @else
                This return is pending approval from Management or Admin before it can be refunded.
            @endif
        </div>
    @elseif ($record->approval_status === 'rejected')
        <div class="rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 text-sm px-4 py-3 mb-6">
            This return was rejected by {{ $record->approvedBy?->name ?? 'Management' }}
            @if ($record->approved_at) on {{ $record->approved_at->format('d M Y') }} @endif.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h3 class="text-lg font-bold text-white mb-4">Return Details</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><p class="text-zinc-500">Customer</p><p class="text-white font-medium">{{ $record->customer?->company_name }}</p></div>
                    <div><p class="text-zinc-500">Source Invoice</p><p class="text-white font-medium">
                        @if ($record->invoice)
                            <a href="{{ route('crm.invoices.show', $record->invoice) }}" class="hover:text-accent-light">{{ $record->invoice->friendly_id }}</a>
                        @else — @endif
                    </p></div>
                    <div><p class="text-zinc-500">Date</p><p class="text-white font-medium">{{ $record->date->format('F jS, Y') }}</p></div>
                    <div><p class="text-zinc-500">Created By</p><p class="text-white font-medium">{{ $record->createdBy?->name ?? '—' }}</p></div>
                    @if ($record->reason)
                        <div class="col-span-2"><p class="text-zinc-500">Reason</p><p class="text-white font-medium">{{ $record->reason }}</p></div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h3 class="text-lg font-bold text-white mb-4">Returned Items</h3>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-zinc-500 text-xs uppercase"><th class="pb-2">Product</th><th class="pb-2 text-right">Qty</th><th class="pb-2 text-right">Amount</th></tr></thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($record->items as $item)
                            <tr>
                                <td class="py-2 text-white font-medium">{{ $item->product?->description }} <span class="block text-zinc-500 text-xs font-normal">{{ $item->product?->code }}</span></td>
                                <td class="py-2 text-right text-zinc-300">{{ $item->qty }}</td>
                                <td class="py-2 text-right text-white">MVR {{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-center text-zinc-500">No items on this return.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h3 class="text-lg font-bold text-white mb-4">Summary</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-white font-bold">Return Value</span>
                        <span class="text-white font-bold text-lg">MVR {{ number_format($record->value, 2) }}</span>
                    </div>
                    @if ($record->refund_applied_at)
                        <div class="flex justify-between text-zinc-400 pt-2 border-t border-white/10">
                            <span>Applied to Invoice</span>
                            <span class="text-green-400 font-medium">{{ $record->refund_applied_at->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between text-zinc-400">
                            <span>Credit Note</span>
                            <a href="{{ route('print.credit-note', $record) }}" target="_blank" class="text-accent-light font-medium hover:underline">
                                CN-{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </div>
                        @if ($record->approvedBy)
                            <div class="flex justify-between text-zinc-400">
                                <span>Approved By</span>
                                <span class="text-white font-medium">{{ $record->approvedBy->name }}</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
