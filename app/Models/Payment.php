<?php

namespace App\Models;

use App\Models\Concerns\HasFriendlyId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasFriendlyId;

    public const METHODS = ['Cash', 'Bank Transfer', 'Cheque', 'Purchase Order'];

    /** Not user-selectable on the Receive Payment form — used only for system-generated refund entries. */
    public const METHOD_REFUND = 'Refund';

    protected $fillable = ['invoice_id', 'date', 'method', 'reference', 'received_by', 'amount'];

    protected $casts = [
        'date' => 'date',
    ];

    public static function friendlyIdPrefix(): string
    {
        return 'PAY';
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
