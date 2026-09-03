<?php

namespace App\Models\Concerns;

use App\Models\RecordNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasNotes
{
    public function notes(): MorphMany
    {
        return $this->morphMany(RecordNote::class, 'notable')->latest();
    }

    public function addNote(?User $user, string $message): RecordNote
    {
        return $this->notes()->create([
            'user_id' => $user?->id,
            'message' => $message,
        ]);
    }
}
