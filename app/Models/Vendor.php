<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = ['company_name', 'contact_person', 'phone', 'location'];

    public function prices(): HasMany
    {
        return $this->hasMany(ProductVendorPrice::class);
    }
}
