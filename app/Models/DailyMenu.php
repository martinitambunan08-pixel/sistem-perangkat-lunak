<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyMenu extends Model
{
    protected $fillable = [
        'menu_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'menu_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(DailyMenuItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
