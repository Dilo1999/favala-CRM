<?php

namespace App\Models;

use App\Models\Concerns\HasFriendlyId;
use App\Models\Concerns\HasNotes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesQuery extends Model
{
    use HasFactory, HasFriendlyId, HasNotes;

    protected $table = 'queries';

    public const STATUS_NEW = 'new';

    public const STATUS_NEGOTIATING = 'negotiating';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_DEAD = 'dead';

    public const STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_NEGOTIATING => 'Negotiating',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_DEAD => 'Dead',
    ];

    protected $fillable = [
        'customer_id', 'phone', 'description', 'tags', 'source', 'deal_id', 'quotation_id',
        'value', 'status', 'follow_up', 'query_source', 'query_type', 'assigned_staff_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'follow_up' => 'boolean',
        'value' => 'decimal:2',
    ];

    public static function friendlyIdPrefix(): string
    {
        return 'QRY';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }
}
