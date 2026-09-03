<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'customer_id', 'assigned_to', 'deadline', 'notes',
        'status', 'completed_by', 'completed_on',
    ];

    protected $casts = [
        'deadline' => 'date',
        'completed_on' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function markComplete(User $user): void
    {
        $this->update([
            'status' => 'completed',
            'completed_by' => $user->id,
            'completed_on' => now(),
        ]);
    }

    public function getDurationAttribute(): ?string
    {
        if (! $this->completed_on || ! $this->created_at) {
            return null;
        }

        return $this->created_at->diffForHumans($this->completed_on, true);
    }
}
