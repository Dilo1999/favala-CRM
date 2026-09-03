<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Island extends Model
{
    use HasFactory;

    protected $fillable = ['atoll_id', 'name'];

    public function atoll(): BelongsTo
    {
        return $this->belongsTo(Atoll::class);
    }
}
