<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Target extends Model
{
    use HasFactory;

    public const SCOPE_COMPANY = 'company';

    public const SCOPE_STAFF = 'staff';

    protected $fillable = [
        'scope', 'user_id', 'period', 'period_start',
        'sales', 'quotations', 'deals', 'meetings', 'calls', 'site_visits', 'new_leads',
    ];

    protected $casts = [
        'period_start' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
