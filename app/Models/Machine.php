<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $fillable = [
        'name',
        'location',
        'ip_address',
        'status',
    ];

    public function slots(): HasMany
    {
        return $this->hasMany(MachineSlot::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(ProductInventory::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(MachineStatusLog::class);
    }

    public function telemetryData(): HasMany
    {
        return $this->hasMany(TelemetryData::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }
}