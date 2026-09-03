<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('crm.deals.show', $record) }}" class="text-gray-400 hover:text-white"><x-heroicon-o-arrow-left class="w-5 h-5" /></a>
        <h1 class="text-2xl font-bold text-white">Edit {{ $record->friendly_id }}</h1>
    </div>

    <form wire:submit.prevent="save" class="max-w-2xl space-y-6">
        <div class="rounded-xl bg-gray-900 border border-gray-800 p-5 grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-xs text-gray-400 mb-1">Customer</label>
                <select wire:model="customer_id" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                    @foreach ($customers as $id => $name) <option value="{{ $id }}">{{ $name }}</option> @endforeach
                </select>
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
                @if ($stage === 'won')
                    <input type="text" value="Won (automatic)" disabled class="w-full rounded-lg bg-gray-800 border-gray-700 text-gray-500 text-sm" />
                @else
                    <select wire:model="stage" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                        <option value="potential">Potential</option>
                        <option value="hot">🔥 Hot Deal</option>
                        <option value="lost">Lost</option>
                    </select>
                @endif
            </div>
            <div class="col-span-2">
                <label class="block text-xs text-gray-400 mb-1">Additional Details</label>
                <textarea wire:model="additional_details" rows="3" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm"></textarea>
            </div>
        </div>

        <div class="flex justify-between">
            <button type="button" wire:click="delete" wire:confirm="Delete this deal?" class="px-4 py-2 rounded-lg border border-red-800 text-red-400 text-sm">Delete Deal</button>
            <div class="flex gap-2">
                <a href="{{ route('crm.deals.show', $record) }}" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Save Changes</button>
            </div>
        </div>
    </form>
</div>
