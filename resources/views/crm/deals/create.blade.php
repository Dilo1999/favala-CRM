<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('crm.deals') }}" class="text-gray-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
        <h1 class="text-2xl font-bold text-white">New Deal</h1>
    </div>

    <form wire:submit.prevent="save" class="max-w-3xl space-y-6">
        <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
            <h2 class="font-bold text-white mb-4">Deal Details</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs text-gray-400 mb-1">Customer</label>
                    <select wire:model="customer_id" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                        <option value="">Select customer…</option>
                        @foreach ($customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                    </select>
                    @error('customer_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Deal Date</label>
                    <input type="date" wire:model="deal_date" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Request Source</label>
                    <select wire:model="request_source" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                        <option value="">Select…</option>
                        @foreach ($requestSources as $val) <option value="{{ $val }}">{{ $val }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Assigned Staff</label>
                    <select wire:model="assigned_staff_id" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                        <option value="">Unassigned</option>
                        @foreach ($staff as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Deal Stage</label>
                    <select wire:model="stage" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                        <option value="potential">Potential</option>
                        <option value="hot">🔥 Hot Deal</option>
                        <option value="lost">Lost</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-gray-400 mb-1">Additional Details</label>
                    <textarea wire:model="additional_details" rows="3" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm"></textarea>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
            <h2 class="font-bold text-white mb-4">Products Requested</h2>
            <div class="space-y-3">
                @foreach ($products as $i => $row)
                    <div class="flex gap-3 items-start">
                        <div class="flex-1">
                            <select wire:model="products.{{ $i }}.product_id" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                                <option value="">Select product…</option>
                                @foreach ($productOptions as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                            </select>
                        </div>
                        <div class="w-28">
                            <input type="number" step="0.01" wire:model="products.{{ $i }}.qty" placeholder="Qty" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                        </div>
                        @if (count($products) > 1)
                            <button type="button" wire:click="removeProductRow({{ $i }})" class="text-red-400 p-2"><x-heroicon-o-trash class="w-4 h-4" /></button>
                        @endif
                    </div>
                @endforeach
            </div>
            <button type="button" wire:click="addProductRow" class="mt-3 text-sm text-orange-400 font-medium">+ Add product</button>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('crm.deals') }}" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Create Deal</button>
        </div>
    </form>
</div>
