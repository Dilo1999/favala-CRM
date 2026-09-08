<?php

namespace App\Models\ShopCatalog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $connection = 'shop_catalog';

    protected $table = 'shop_products';

    protected $fillable = ['code', 'description', 'category', 'brand', 'image_path'];

    public function prices(): HasMany
    {
        return $this->hasMany(ShopProductPrice::class)->latest('id');
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_product_prices')
            ->withPivot('price')
            ->withTimestamps();
    }

    /** Cheapest shop currently carrying this product. */
    public function cheapestPrice(): ?ShopProductPrice
    {
        return $this->prices->sortBy('price')->first();
    }
}
