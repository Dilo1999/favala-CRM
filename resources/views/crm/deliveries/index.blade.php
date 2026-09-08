<div>
    <h1 class="text-2xl font-bold text-white mb-6">Deliveries</h1>

    <div class="relative mb-4 max-w-md">
        <x-heroicon-o-search class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by ID, customer or location…" class="w-full pl-9 rounded-lg bg-zinc-800 border-white/10 text-white text-sm" />
    </div>

    <div class="rounded-xl border border-white/10 bg-zinc-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                    <th class="p-3">Delivery ID</th><th class="p-3">Customer</th><th class="p-3">Contact</th>
                    <th class="p-3">Location</th><th class="p-3">Status</th><th class="p-3">Date & Time</th><th class="p-3">Time Left</th><th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse ($deliveries as $delivery)
                    <tr class="hover:bg-zinc-700/40">
                        <td class="p-3 text-white font-medium">{{ $delivery->friendly_id }}</td>
                        <td class="p-3 text-zinc-300">{{ $delivery->customer?->company_name }}</td>
                        <td class="p-3 text-zinc-400">{{ $delivery->contact_name }}<span class="block text-xs">{{ $delivery->contact_phone }}</span></td>
                        <td class="p-3 text-zinc-400">{{ $delivery->location }}</td>
                        <td class="p-3"><x-badge :color="$delivery->status === 'completed' ? 'green' : 'orange'">{{ ucfirst($delivery->status) }}</x-badge></td>
                        <td class="p-3 text-zinc-400">{{ optional($delivery->deadline_date)->format('d M Y') }} {{ $delivery->deadline_time }}</td>
                        <td class="p-3 {{ $delivery->isOverdue() ? 'text-red-400' : 'text-zinc-400' }}">{{ $delivery->time_left }}</td>
                        <td class="p-3 text-right">
                            <x-row-menu>
                                <a href="{{ route('crm.deliveries.show', $delivery) }}" class="block px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">View</a>
                                <button wire:click="editDelivery({{ $delivery->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Edit</button>
                                <a href="{{ route('print.delivery', $delivery) }}" target="_blank" class="block px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Print</a>
                                @if ($delivery->status === 'pending')
                                    <button wire:click="rescheduleDelivery({{ $delivery->id }})" class="block w-full text-left px-3 py-1.5 text-zinc-200 hover:bg-zinc-700">Reschedule</button>
                                    <button wire:click="markComplete({{ $delivery->id }})" class="block w-full text-left px-3 py-1.5 text-green-400 hover:bg-zinc-700">Mark as Complete</button>
                                @endif
                                <button wire:click="delete({{ $delivery->id }})" wire:confirm="Delete this delivery?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-zinc-700">Delete</button>
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-6 text-center text-zinc-500">No deliveries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $deliveries->links() }}</div>

    @include('crm.deliveries._edit-modal')
    @include('crm.deliveries._reschedule-modal')
</div>
