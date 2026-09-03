<?php

namespace App\Models;

use App\Models\Concerns\HasFriendlyId;
use App\Models\Concerns\HasNotes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    use HasFactory, HasFriendlyId, HasNotes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'invoice_id', 'customer_id', 'contact_name', 'contact_phone', 'location',
        'deadline_date', 'deadline_time', 'status', 'completed_at',
    ];

    protected $casts = [
        'deadline_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public static function friendlyIdPrefix(): string
    {
        return 'DEL';
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class);
    }

    /**
     * The effective deadline instant. A date with no time specified defaults to end-of-day,
     * not midnight — otherwise "today" deadlines show as Overdue the instant they're created (defect #9).
     */
    public function getDeadlineAtAttribute(): ?Carbon
    {
        if (! $this->deadline_date) {
            return null;
        }

        $date = $this->deadline_date->toDateString();

        return $this->deadline_time
            ? Carbon::parse($date.' '.$this->deadline_time)
            : Carbon::parse($date)->endOfDay();
    }

    public function getTimeLeftAttribute(): string
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return 'Completed';
        }

        $deadline = $this->deadline_at;
        if (! $deadline) {
            return 'No deadline set';
        }

        return $deadline->isPast() ? 'Overdue' : $deadline->diffForHumans();
    }

    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_COMPLETED && $this->deadline_at && $this->deadline_at->isPast();
    }

    public function markComplete(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED, 'completed_at' => now()]);
    }
}
