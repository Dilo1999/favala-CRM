<div>
    <h1 class="text-2xl font-bold text-white mb-6">Settings</h1>

    <div class="flex rounded-lg border border-white/10 w-fit mb-6 overflow-hidden text-sm">
        <button wire:click="$set('tab', 'general')" class="px-4 py-2 font-medium {{ $tab === 'general' ? 'bg-accent text-white' : 'text-zinc-400' }}">General</button>
        <button wire:click="$set('tab', 'users')" class="px-4 py-2 font-medium {{ $tab === 'users' ? 'bg-accent text-white' : 'text-zinc-400' }}">Users & Roles</button>
    </div>

    @if ($tab === 'general')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            @foreach ($groups as $key => $group)
                <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                    <h3 class="font-bold text-white">{{ $group['label'] }}</h3>
                    <p class="text-xs text-zinc-500 mb-3">Used in: {{ str_replace('_', ' ', $key) }} dropdowns.</p>
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach ($group['options'] as $option)
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full bg-zinc-700 text-zinc-200">
                                {{ $option->value }}
                                <button wire:click="removeOption({{ $option->id }})" class="text-zinc-500 hover:text-red-400">&times;</button>
                            </span>
                        @endforeach
                    </div>
                    <form wire:submit.prevent="addOption('{{ $key }}')" class="flex gap-2">
                        <input type="text" wire:model="newValue.{{ $key }}" placeholder="Add option…" class="flex-1 rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-accent text-white text-xs font-semibold">+ Add</button>
                    </form>
                </div>
            @endforeach
        </div>
        <button wire:click="saveChanges" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Save Changes</button>
    @else
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-bold text-white">Team Members</h2>
                <p class="text-sm text-zinc-500 mt-1">A list of all users in your workspace.</p>
            </div>
            <button wire:click="$set('showInvite', true)" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">
                <x-heroicon-o-plus-circle class="w-4 h-4" /> Invite Member
            </button>
        </div>
        <div class="rounded-xl border border-white/10 bg-zinc-800 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-500 text-xs uppercase border-b border-white/10">
                        <th class="px-6 py-3">Name</th><th class="px-6 py-3">Email</th><th class="px-6 py-3">Role</th><th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @foreach ($users as $user)
                        <tr class="hover:bg-zinc-700/40">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($user->avatar)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-9 w-9 rounded-full object-cover shrink-0" />
                                    @else
                                        <div class="h-9 w-9 rounded-full bg-accent/20 text-accent flex items-center justify-center font-semibold shrink-0">
                                            {{ mb_substr($user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <span class="text-white font-medium">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-zinc-400">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <select wire:change="updateUserRole({{ $user->id }}, $event.target.value)" class="rounded-lg bg-zinc-700 border-white/10 text-white text-xs">
                                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    <option value="member" @selected($user->role === 'member')>Member</option>
                                    <option value="management" @selected($user->role === 'management')>Management</option>
                                </select>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="toggleUserStatus({{ $user->id }})"
                                            class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors {{ $user->status === 'active' ? 'bg-accent' : 'bg-zinc-600' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $user->status === 'active' ? 'translate-x-4' : 'translate-x-0.5' }}"></span>
                                    </button>
                                    <span class="text-sm {{ $user->status === 'active' ? 'text-zinc-200' : 'text-zinc-500' }}">{{ ucfirst($user->status) }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($showInvite)
            <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showInvite', false)">
                <div class="bg-zinc-800 border border-white/10 rounded-xl w-full max-w-md p-6">
                    <h2 class="text-lg font-bold text-white mb-4">Invite Member</h2>
                    <form wire:submit.prevent="inviteMember" class="space-y-3">
                        <div>
                            <label class="block text-xs text-zinc-400 mb-1">Name</label>
                            <input type="text" wire:model="inviteForm.name" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                            @error('inviteForm.name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-400 mb-1">Email</label>
                            <input type="email" wire:model="inviteForm.email" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                            @error('inviteForm.email') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-400 mb-1">Temporary Password</label>
                            <input type="text" wire:model="inviteForm.password" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                            @error('inviteForm.password') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-400 mb-1">Role</label>
                            <select wire:model="inviteForm.role" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                                <option value="member">Member</option>
                                <option value="management">Management</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 mt-2">
                            <button type="button" wire:click="$set('showInvite', false)" class="px-4 py-2 rounded-lg border border-white/10 text-zinc-300 text-sm">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-accent hover:bg-accent-hover text-white text-sm font-semibold">Send Invite</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
