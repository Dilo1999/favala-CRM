<?php

namespace App\Models;

use App\Models\Concerns\HasFriendlyId;
use App\Models\Concerns\HasNotes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Deal extends Model
{
    use HasFactory, HasFriendlyId, HasNotes;

    public const STAGE_POTENTIAL = 'potential';

    public const STAGE_HOT = 'hot';

    public const STAGE_LOST = 'lost';

    public const STAGE_WON = 'won';

    protected $fillable = [
        'customer_id', 'deal_date', 'request_source', 'assigned_staff_id',
        'stage', 'additional_details', 'expires_at', 'converted_at', 'created_by',
    ];

    protected $casts = [
        'deal_date' => 'date',
        'expires_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public static function friendlyIdPrefix(): string
    {
        return 'DEAL';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function products(): HasMany
    {
        return $this->hasMany(DealProduct::class);
    }

    public function salesQuery(): HasOne
    {
        return $this->hasOne(SalesQuery::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function isExpired(): bool
    {
        return ! $this->isConverted()
            && $this->stage !== self::STAGE_LOST
            && $this->expires_at
            && $this->expires_at->isPast();
    }

    public function isConverted(): bool
    {
        return $this->stage === self::STAGE_WON || $this->converted_at !== null;
    }

    public function isInProgress(): bool
    {
        return ! $this->isConverted()
            && ! $this->isExpired()
            && in_array($this->stage, [self::STAGE_POTENTIAL, self::STAGE_HOT], true);
    }

    /** Derived, display-only status badge (spec: In Progress / Converted / Expired / Lost). */
    public function getOutcomeStatusAttribute(): string
    {
        if ($this->isConverted()) {
            return 'Converted';
        }
        if ($this->stage === self::STAGE_LOST) {
            return 'Lost';
        }
        if ($this->isExpired()) {
            return 'Expired';
        }

        return 'In Progress';
    }

    /** Marks the deal Won automatically — called only when it converts through to an invoice. */
    public function markWon(): void
    {
        $this->update(['stage' => self::STAGE_WON, 'converted_at' => now()]);
    }

    /**
     * Creates the linked Query in "Negotiating", tagged Deal + product category, carrying
     * the deal's notes (spec §5). Call once, after the deal's product lines are persisted.
     */
    public function spawnQuery(): SalesQuery
    {
        $categories = $this->products()
            ->with('product')
            ->get()
            ->pluck('product.category')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $this->salesQuery()->create([
            'customer_id' => $this->customer_id,
            'phone' => $this->customer?->phone,
            'description' => "Auto-generated from Deal #{$this->friendly_id}".($this->additional_details ? ' — '.$this->additional_details : ''),
            'tags' => array_values(array_unique(array_merge(['Deal'], $categories))),
            'source' => 'deal',
            'status' => SalesQuery::STATUS_NEGOTIATING,
            'assigned_staff_id' => $this->assigned_staff_id,
        ]);
    }
}
