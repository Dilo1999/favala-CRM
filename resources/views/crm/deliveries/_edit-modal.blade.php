@if ($deliveryEditingId)
    <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="closeDeliveryEdit">
        <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-md p-6">
            <h2 class="text-lg font-bold text-white mb-4">Edit Delivery</h2>
            <form wire:submit.prevent="saveDeliveryEdit" class="space-y-4">
                <div>
                    <label class="block text-xs text-zinc-400 mb-1">Delivery Location</label>
                    <textarea wire:model="deliveryEditForm.location" rows="2" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm"></textarea>
                    @error('deliveryEditForm.location') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Contact Person</label>
                        <input type="text" wire:model="deliveryEditForm.contact_name" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Contact Phone</label>
                        <input type="text" wire:model="deliveryEditForm.contact_phone" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-2">
                    <button type="button" wire:click="closeDeliveryEdit" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endif
