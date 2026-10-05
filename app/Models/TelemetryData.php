<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelemetryData extends Model
{
    protected $fillable = [
        'machine_id',
        'temperature',
        'humidity',
        'voltage',
        'sensor_status',
        'sensor_data',
        'recorded_at',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'voltage' => 'decimal:2',
        'sensor_data' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}