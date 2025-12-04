<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisasterPrediction extends Model
{
    use HasFactory;

   protected $fillable = [
        'disaster_type',
        'description',
        'severity',
        'affected_areas',
        'probability',
        'latitude',
        'longitude',
        'location_name',
        'risk_level',
        'predicted_recovery_days',
        'predicted_date',
        'valid_until',
        'predicted_at',
        'user_id',
    ];

    protected $casts = [
        'predicted_at' => 'datetime',
        'predicted_date' => 'datetime',
        'valid_until' => 'datetime',
        'probability' => 'decimal:2',
    ];
}
