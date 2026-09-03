<div>
    <h1 class="text-2xl font-bold text-white mb-6">Sales Returns</h1>

    <div class="relative mb-4 max-w-md">
        <x-heroicon-o-search class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search by id, customer or invoice…" class="w-full pl-9 rounded-lg bg-gray-900 border-gray-700 text-white text-sm" />
    </div>

    <div class="rounded-xl border border-gray-800 bg-gray-900 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase border-b border-gray-800">
                    <th class="p-3">Return ID</th><th class="p-3">Date</th><th class="p-3">Invoice ID</th><th class="p-3">Customer</th><th class="p-3">Status</th><th class="p-3">Value</th><th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse ($returns as $return)
                    <tr class="hover:bg-gray-800/40">
                        <td class="p-3 text-white font-medium">{{ $return->friendly_id }}</td>
                        <td class="p-3 text-gray-400">{{ $return->date->format('d M Y') }}</td>
                        <td class="p-3 text-gray-300">{{ $return->invoice?->friendly_id }}</td>
                        <td class="p-3 text-gray-300">{{ $return->customer?->company_name }}</td>
                        <td class="p-3">
                            <x-badge :color="match($return->status) { 'refunded' => 'green', 'processed' => 'blue', default => 'orange' }">
                                {{ \App\Models\SalesReturn::STATUSES[$return->status] }}
                            </x-badge>
                        </td>
                        <td class="p-3 text-white">MVR {{ number_format($return->value, 2) }}</td>
                        <td class="p-3 text-right">
                            <x-row-menu>
                                @foreach (\App\Models\SalesReturn::STATUSES as $val => $label)
                                    <button wire:click="updateStatus({{ $return->id }}, '{{ $val }}')" class="block w-full text-left px-3 py-1.5 text-gray-200 hover:bg-gray-700">Mark {{ $label }}</button>
                                @endforeach
                            </x-row-menu>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-6 text-center text-gray-500">No returns found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $returns->links() }}</div>
</div>
