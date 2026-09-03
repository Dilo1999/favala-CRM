<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Lightweight per-record chat/update timeline shared by Deals, Deliveries and Queries (spec §4).
 */
class RecordNote extends Model
{
    use HasFactory;

    protected $fillable = ['notable_type', 'notable_id', 'user_id', 'message'];

    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
