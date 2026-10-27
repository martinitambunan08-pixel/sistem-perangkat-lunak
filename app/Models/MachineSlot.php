<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineSlot extends Model
{
    protected $fillable = [
        'machine_id',
        'product_id',
        'slot_code',
        'capacity',
        'current_qty',
        'hold_qty',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'current_stock' => 'integer',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}