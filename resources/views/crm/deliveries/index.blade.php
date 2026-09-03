<div>
    <h1 class="text-2xl font-bold text-white mb-6">Deliveries</h1>

    <div class="relative mb-4 max-w-md">
        <x-heroicon-o-search class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by ID, customer or location…" class="w-full pl-9 rounded-lg bg-gray-900 border-gray-700 text-white text-sm" />
    </div>

    <div class="rounded-xl border border-gray-800 bg-gray-900 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase border-b border-gray-800">
                    <th class="p-3">Delivery ID</th><th class="p-3">Customer</th><th class="p-3">Contact</th>
                    <th class="p-3">Location</th><th class="p-3">Status</th><th class="p-3">Date & Time</th><th class="p-3">Time Left</th><th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse ($deliveries as $delivery)
                    <tr class="hover:bg-gray-800/40">
                        <td class="p-3 text-white font-medium">{{ $delivery->friendly_id }}</td>
                        <td class="p-3 text-gray-300">{{ $delivery->customer?->company_name }}</td>
                        <td class="p-3 text-gray-400">{{ $delivery->contact_name }}<span class="block text-xs">{{ $delivery->contact_phone }}</span></td>
                        <td class="p-3 text-gray-400">{{ $delivery->location }}</td>
                        <td class="p-3"><x-badge :color="$delivery->status === 'completed' ? 'green' : 'orange'">{{ ucfirst($delivery->status) }}</x-badge></td>
                        <td class="p-3 text-gray-400">{{ optional($delivery->deadline_date)->format('d M Y') }} {{ $delivery->deadline_time }}</td>
                        <td class="p-3 {{ $delivery->isOverdue() ? 'text-red-400' : 'text-gray-400' }}">{{ $delivery->time_left }}</td>
                        <td class="p-3 text-right">
                            <x-row-menu>
                                <a href="{{ route('crm.deliveries.show', $delivery) }}" class="block px-3 py-1.5 text-gray-200 hover:bg-gray-700">View / Edit</a>
                                <a href="{{ route('print.delivery', $delivery) }}" target="_blank" class="block px-3 py-1.5 text-gray-200 hover:bg-gray-700">Print</a>
                                @if ($delivery->status === 'pending')
                                    <button wire:click="markComplete({{ $delivery->id }})" class="block w-full text-left px-3 py-1.5 text-emerald-400 hover:bg-gray-700">Mark as Complete</button>
                                @endif
                                <button wire:click="delete({{ $delivery->id }})" wire:confirm="Delete this delivery?" class="block w-full text-left px-3 py-1.5 text-red-400 hover:bg-gray-700">Delete</button>
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-6 text-center text-gray-500">No deliveries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $deliveries->links() }}</div>
</div>
