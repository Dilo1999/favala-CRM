<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\CrmAccountApproved;

class UserObserver
{
    /**
     * Fires however the status change happens (Filament's "Approve" row action or a plain
     * edit-form save) — single source of truth so the approval email is never sent twice.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged('status') && $user->status === 'active' && $user->getOriginal('status') !== 'active') {
            $user->notify(new CrmAccountApproved());
        }
    }
}
