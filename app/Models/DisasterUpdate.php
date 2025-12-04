<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisasterUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'disaster_type',
        'title',
        'description',
        'severity',
        'source',
        'latitude',
        'longitude',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];
}
