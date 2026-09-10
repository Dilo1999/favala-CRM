<?php

namespace App\Http\Livewire\Crm;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileMenu extends Component
{
    use WithFileUploads;

    public bool $showEditModal = false;

    public string $name = '';

    public $avatar = null;

    public ?string $existingAvatarPath = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'avatar' => 'nullable|image|max:2048',
        ];
    }

    public function openEdit(): void
    {
        $this->name = auth()->user()->name;
        $this->existingAvatarPath = auth()->user()->avatar;
        $this->avatar = null;
        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function removeAvatar(): void
    {
        $this->avatar = null;
        $this->existingAvatarPath = null;
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();
        $avatarPath = $user->avatar;

        if ($this->avatar) {
            if ($avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }
            $avatarPath = $this->avatar->store('avatars', 'public');
        } elseif ($this->existingAvatarPath === null && $avatarPath) {
            Storage::disk('public')->delete($avatarPath);
            $avatarPath = null;
        }

        $user->update(['name' => $this->name, 'avatar' => $avatarPath]);

        $this->showEditModal = false;
        session()->flash('status', 'Profile updated.');
    }

    public function render()
    {
        return view('livewire.crm.profile-menu');
    }
}
