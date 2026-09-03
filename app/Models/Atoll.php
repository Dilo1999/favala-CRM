<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atoll extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    public function islands(): HasMany
    {
        return $this->hasMany(Island::class);
    }
}
