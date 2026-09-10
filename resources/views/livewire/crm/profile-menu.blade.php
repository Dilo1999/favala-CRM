<div x-data="{ open: false }" @click.outside="open = false" class="relative flex items-center gap-3">
    <button type="button" @click="open = !open"
        class="flex items-center gap-3 rounded-lg px-1.5 py-1 hover:bg-zinc-800 transition-colors">
        <div class="text-right hidden sm:block">
            <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
            <p class="text-xs text-zinc-500">{{ ucfirst(auth()->user()->role) }}</p>
        </div>
        @if (auth()->user()->avatar)
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                class="h-9 w-9 rounded-full object-cover shrink-0" />
        @else
            <div class="h-9 w-9 rounded-full bg-accent/20 text-accent flex items-center justify-center font-semibold shrink-0">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </div>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition
        class="absolute right-0 top-full mt-2 z-30 w-60 rounded-xl bg-zinc-800 border border-white/10 shadow-xl py-2 text-sm">
        <div class="flex items-center gap-3 px-4 py-3 border-b border-white/10">
            @if (auth()->user()->avatar)
                <img src="{{ auth()->user()->avatar_url }}" alt=""
                    class="h-11 w-11 rounded-full object-cover shrink-0" />
            @else
                <div class="h-11 w-11 rounded-full bg-accent/20 text-accent flex items-center justify-center font-semibold shrink-0">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-white font-semibold truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>

        <button type="button" wire:click="openEdit" @click="open = false"
            class="w-full flex items-center gap-2 text-left px-4 py-2 text-zinc-200 hover:bg-zinc-700">
            <x-heroicon-o-pencil class="w-4 h-4 text-zinc-400" /> Edit Profile
        </button>

        <div class="border-t border-white/10 my-1"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-2 text-left px-4 py-2 text-red-400 hover:bg-zinc-700">
                <x-heroicon-o-logout class="w-4 h-4" /> Log out
            </button>
        </form>
    </div>

    @if ($showEditModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-40"
            wire:click.self="$set('showEditModal', false)">
            <div class="bg-zinc-800 border border-white/10 rounded-2xl w-full max-w-sm p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-white mb-5">Edit Profile</h2>
                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="flex flex-col items-center gap-2">
                        <div class="h-20 w-20 rounded-full border border-white/10 bg-zinc-900 flex items-center justify-center overflow-hidden shrink-0">
                            @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="h-full w-full object-cover" />
                            @elseif ($existingAvatarPath)
                                <img src="{{ auth()->user()->avatar_url }}" class="h-full w-full object-cover" />
                            @else
                                <span class="text-2xl font-semibold text-accent">{{ mb_substr($name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="text-xs text-accent hover:text-accent-hover font-medium cursor-pointer">
                                Change photo
                                <input type="file" wire:model="avatar" accept="image/*" class="hidden" />
                            </label>
                            @if ($avatar || $existingAvatarPath)
                                <button type="button" wire:click="removeAvatar" class="text-xs text-red-400 hover:text-red-300">Remove</button>
                            @endif
                        </div>
                        <div wire:loading wire:target="avatar" class="text-xs text-zinc-500">Uploading…</div>
                        @error('avatar') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">Name</label>
                        <input type="text" wire:model="name"
                            class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm focus:border-accent" />
                        @error('name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showEditModal', false)"
                            class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm hover:bg-zinc-700 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold shadow-sm transition-colors">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
