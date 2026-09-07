<?php

namespace App\Models;

use App\Models\Concerns\HasFriendlyId;
use App\Models\Concerns\HasNotes;
use Carbon\CarbonInterface;
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

    public function isConverted(): bool
    {
        return $this->stage === self::STAGE_WON || $this->converted_at !== null;
    }

    public function isExpired(): bool
    {
        return ! $this->isConverted() && $this->expires_at && $this->expires_at->isPast();
    }

    public function isInProgress(): bool
    {
        return ! $this->isConverted() && ! $this->isExpired();
    }

    /**
     * Badge #1 — purely time/conversion-based, independent of the manually-set stage
     * (spec §6.6: two status badges shown together, e.g. Converted+Won, Expired+Lost,
     * Expired+Potential — this is the first of the pair).
     */
    public function getOutcomeStatusAttribute(): string
    {
        if ($this->isConverted()) {
            return 'Converted';
        }
        if ($this->isExpired()) {
            return 'Expired';
        }

        return 'In Progress';
    }

    /** Badge #2 — the deal's actual stage, shown alongside the outcome badge above. */
    public function getStageLabelAttribute(): string
    {
        return match ($this->stage) {
            self::STAGE_HOT => '🔥 Hot Deal',
            self::STAGE_WON => 'Won',
            self::STAGE_LOST => 'Lost',
            default => 'Potential',
        };
    }

    /** The aging/outcome line under the card timestamp (spec §6.6). */
    public function getAgingLineAttribute(): string
    {
        if ($this->isConverted()) {
            $end = $this->converted_at ?? $this->updated_at;

            return 'Converted in '.$this->created_at->diffForHumans($end, CarbonInterface::DIFF_ABSOLUTE);
        }

        if ($this->isExpired()) {
            return 'Expired after '.$this->created_at->diffForHumans($this->expires_at, CarbonInterface::DIFF_ABSOLUTE);
        }

        return 'In progress for '.$this->created_at->diffForHumans(now(), CarbonInterface::DIFF_ABSOLUTE);
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
        $dealProducts = $this->products()->with('product')->get();

        $categories = $dealProducts->pluck('product.category')->filter()->unique()->values()->all();

        $productList = $dealProducts
            ->map(fn (DealProduct $dp) => $dp->product
                ? "{$dp->product->description} (Qty: ".SalesQuery::formatQty((float) $dp->qty).')'
                : null)
            ->filter()
            ->implode(', ');

        $description = "Auto-generated from Deal #{$this->friendly_id}";
        if ($productList) {
            $description .= ' — '.$productList;
        }
        if ($this->additional_details) {
            $description .= ' — '.$this->additional_details;
        }

        return $this->salesQuery()->create([
            'customer_id' => $this->customer_id,
            'phone' => $this->customer?->phone,
            'description' => $description,
            'tags' => array_values(array_unique(array_merge(['Deal'], $categories))),
            'source' => 'deal',
            'status' => SalesQuery::STATUS_NEGOTIATING,
            'assigned_staff_id' => $this->assigned_staff_id,
        ]);
    }
}
