<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'legacy_code', 'description', 'category', 'brand', 'unit_of_measure', 'shop_catalog_product_id'];

    /** Which database this product's record originated from, for display badges. */
    public function getSourceLabelAttribute(): string
    {
        return $this->shop_catalog_product_id ? 'Shop Catalog' : 'CRM';
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductVendorPrice::class)->latest('id');
    }

    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'product_vendor_prices')
            ->withPivot('price', 'added_by', 'created_at')
            ->withTimestamps();
    }

    /**
     * The most recently entered price per vendor for this product (business rule §6.13).
     * Computed in PHP (not a correlated-subquery relation) so it stays correct whether
     * `prices` was lazy-loaded or eager-loaded across many products at once.
     */
    public function currentPrices(): \Illuminate\Support\Collection
    {
        return $this->prices->unique('vendor_id')->values();
    }

    /** Cheapest current vendor cost — used to auto-select a vendor on a new quotation line. */
    public function cheapestCurrentPrice(): ?ProductVendorPrice
    {
        return $this->currentPrices()->sortBy('price')->first();
    }
}
