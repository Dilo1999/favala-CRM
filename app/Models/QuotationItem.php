<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'product_id', 'vendor_id', 'qty', 'cost', 'markup_percent',
        'discount_type', 'discount_value', 'unit_price', 'line_amount', 'sort_order',
    ];

    protected $attributes = [
        'discount_type' => 'flat',
        'discount_value' => 0,
        'markup_percent' => 15,
        'qty' => 1,
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
