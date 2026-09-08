<?php

namespace App\Models\ShopCatalog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;

    protected $connection = 'shop_catalog';

    protected $fillable = ['name', 'contact_person', 'phone', 'location'];

    public function prices(): HasMany
    {
        return $this->hasMany(ShopProductPrice::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'shop_product_prices')
            ->withPivot('price')
            ->withTimestamps();
    }
}
