<div>
    <h1 class="text-2xl font-bold text-white mb-6">Settings</h1>

    <div class="flex rounded-lg border border-gray-800 w-fit mb-6 overflow-hidden text-sm">
        <button wire:click="$set('tab', 'general')" class="px-4 py-2 font-medium {{ $tab === 'general' ? 'bg-orange-600 text-white' : 'text-gray-400' }}">General</button>
        <button wire:click="$set('tab', 'users')" class="px-4 py-2 font-medium {{ $tab === 'users' ? 'bg-orange-600 text-white' : 'text-gray-400' }}">Users & Roles</button>
    </div>

    @if ($tab === 'general')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            @foreach ($groups as $key => $group)
                <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                    <h3 class="font-bold text-white">{{ $group['label'] }}</h3>
                    <p class="text-xs text-gray-500 mb-3">Used in: {{ str_replace('_', ' ', $key) }} dropdowns.</p>
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach ($group['options'] as $option)
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full bg-gray-800 text-gray-200">
                                {{ $option->value }}
                                <button wire:click="removeOption({{ $option->id }})" class="text-gray-500 hover:text-red-400">&times;</button>
                            </span>
                        @endforeach
                    </div>
                    <form wire:submit.prevent="addOption('{{ $key }}')" class="flex gap-2">
                        <input type="text" wire:model="newValue.{{ $key }}" placeholder="Add option…" class="flex-1 rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-orange-600 text-white text-xs font-semibold">+ Add</button>
                    </form>
                </div>
            @endforeach
        </div>
        <button wire:click="saveChanges" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Save Changes</button>
    @else
        <div class="flex justify-end mb-4">
            <button wire:click="$set('showInvite', true)" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Invite Member</button>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 text-xs uppercase border-b border-gray-800">
                        <th class="p-3">Name</th><th class="p-3">Email</th><th class="p-3">Role</th><th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach ($users as $user)
                        <tr>
                            <td class="p-3 text-white">{{ $user->name }}</td>
                            <td class="p-3 text-gray-400">{{ $user->email }}</td>
                            <td class="p-3">
                                <select wire:change="updateUserRole({{ $user->id }}, $event.target.value)" class="rounded-lg bg-gray-800 border-gray-700 text-white text-xs">
                                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    <option value="member" @selected($user->role === 'member')>Member</option>
                                    <option value="editor" @selected($user->role === 'editor')>Editor</option>
                                    <option value="viewer" @selected($user->role === 'viewer')>Viewer</option>
                                </select>
                            </td>
                            <td class="p-3">
                                <button wire:click="toggleUserStatus({{ $user->id }})">
                                    <x-badge :color="$user->status === 'active' ? 'green' : 'gray'">{{ ucfirst($user->status) }}</x-badge>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($showInvite)
            <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-30" wire:click.self="$set('showInvite', false)">
                <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-md p-6">
                    <h2 class="text-lg font-bold text-white mb-4">Invite Member</h2>
                    <form wire:submit.prevent="inviteMember" class="space-y-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Name</label>
                            <input type="text" wire:model="inviteForm.name" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                            @error('inviteForm.name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Email</label>
                            <input type="email" wire:model="inviteForm.email" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                            @error('inviteForm.email') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Temporary Password</label>
                            <input type="text" wire:model="inviteForm.password" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm" />
                            @error('inviteForm.password') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Role</label>
                            <select wire:model="inviteForm.role" class="w-full rounded-lg bg-gray-800 border-gray-700 text-white text-sm">
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 mt-2">
                            <button type="button" wire:click="$set('showInvite', false)" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 text-sm">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-500 text-white text-sm font-semibold">Send Invite</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
