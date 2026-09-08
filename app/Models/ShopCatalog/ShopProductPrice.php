<?php

namespace App\Models\ShopCatalog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopProductPrice extends Model
{
    use HasFactory;

    protected $connection = 'shop_catalog';

    protected $fillable = ['shop_id', 'product_id', 'price'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
