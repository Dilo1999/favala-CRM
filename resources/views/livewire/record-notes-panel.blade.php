<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
    <h3 class="text-sm font-bold text-gray-950 dark:text-white mb-1">Updates</h3>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Internal staff notes — not visible to the customer.</p>

    <form wire:submit.prevent="addNote" class="flex gap-2 mb-4">
        <input
            type="text"
            wire:model.defer="message"
            placeholder="Post an update…"
            class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500"
        />
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-500">
            Post
        </button>
    </form>
    @error('message') <p class="text-xs text-danger-600 -mt-3 mb-3">{{ $message }}</p> @enderror

    <div class="space-y-3 max-h-96 overflow-y-auto">
        @forelse ($this->notes as $note)
            <div class="flex gap-3 text-sm">
                <div class="h-8 w-8 shrink-0 rounded-full bg-primary-600/10 text-primary-600 dark:text-primary-400 flex items-center justify-center font-semibold">
                    {{ mb_substr($note->user->name ?? '?', 0, 1) }}
                </div>
                <div>
                    <p class="text-gray-950 dark:text-white">
                        <span class="font-semibold">{{ $note->user->name ?? 'System' }}</span>
                        <span class="text-gray-400 text-xs ms-1">{{ $note->created_at->diffForHumans() }}</span>
                    </p>
                    <p class="text-gray-600 dark:text-gray-300">{{ $note->message }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">No updates yet.</p>
        @endforelse
    </div>
</div>
