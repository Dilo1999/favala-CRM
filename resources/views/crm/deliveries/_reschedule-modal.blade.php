@if ($deliveryReschedulingId)
    <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="closeDeliveryReschedule">
        <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-sm p-6">
            <h2 class="text-lg font-bold text-white mb-4">Reschedule Delivery</h2>
            <form wire:submit.prevent="saveDeliveryReschedule" class="space-y-4">
                <div>
                    <label class="block text-xs text-zinc-400 mb-1">Delivery Deadline</label>
                    <input type="date" wire:model="deliveryRescheduleForm.deadline_date" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                    @error('deliveryRescheduleForm.deadline_date') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs text-zinc-400 mb-1">Time <span class="text-zinc-500">(optional)</span></label>
                    <input type="time" wire:model="deliveryRescheduleForm.deadline_time" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                </div>
                <div class="flex justify-end gap-2 mt-2">
                    <button type="button" wire:click="closeDeliveryReschedule" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Save New Deadline</button>
                </div>
            </form>
        </div>
    </div>
@endif
