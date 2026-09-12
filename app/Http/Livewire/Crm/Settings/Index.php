<?php

namespace App\Http\Livewire\Crm\Settings;

use App\Models\SettingOption;
use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    public string $tab = 'general';

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
    }

    public array $newValue = [];

    public bool $showInvite = false;

    public array $inviteForm = ['name' => '', 'email' => '', 'password' => '', 'role' => 'member'];

    public function addOption(string $group): void
    {
        $value = trim($this->newValue[$group] ?? '');
        if ($value === '') {
            return;
        }

        SettingOption::firstOrCreate(['group' => $group, 'value' => $value], [
            'sort_order' => SettingOption::where('group', $group)->max('sort_order') + 1,
        ]);

        $this->newValue[$group] = '';
    }

    public function removeOption(int $id): void
    {
        SettingOption::findOrFail($id)->delete();
    }

    public function saveChanges(): void
    {
        session()->flash('status', 'Settings saved.');
    }

    public function toggleUserStatus(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
    }

    public function updateUserRole(int $id, string $role): void
    {
        User::whereKey($id)->update(['role' => $role]);
    }

    public function inviteMember(): void
    {
        $this->validate([
            'inviteForm.name' => 'required|string|max:191',
            'inviteForm.email' => 'required|email|unique:users,email',
            'inviteForm.password' => 'required|string|min:6',
            'inviteForm.role' => 'required|in:admin,member,management',
        ]);

        User::create($this->inviteForm + ['status' => 'active']);

        $this->inviteForm = ['name' => '', 'email' => '', 'password' => '', 'role' => 'member'];
        $this->showInvite = false;
        session()->flash('status', 'Team member invited.');
    }

    public function render()
    {
        $groups = collect(SettingOption::GROUPS)->mapWithKeys(fn ($label, $group) => [
            $group => [
                'label' => $label,
                'options' => SettingOption::where('group', $group)->orderBy('sort_order')->orderBy('value')->get(),
            ],
        ]);

        return view('crm.settings.index', [
            'groups' => $groups,
            'users' => User::orderBy('name')->get(),
        ])->layout('layouts.crm');
    }
}
