<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_POTENTIAL = 'potential';

    public const STATUS_NOT_QUALIFIED = 'not_qualified';

    public const STATUS_CUSTOMER = 'customer';

    public const STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_POTENTIAL => 'Potential',
        self::STATUS_NOT_QUALIFIED => 'Not Qualified',
        self::STATUS_CUSTOMER => 'Customer',
    ];

    protected $fillable = [
        'company_name', 'contact_person', 'phone', 'tin', 'atoll_id', 'island_id',
        'address', 'customer_type', 'lead_source', 'assigned_staff_id', 'status', 'added_by',
    ];

    public function atoll(): BelongsTo
    {
        return $this->belongsTo(Atoll::class);
    }

    public function island(): BelongsTo
    {
        return $this->belongsTo(Island::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function queries(): HasMany
    {
        return $this->hasMany(SalesQuery::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
