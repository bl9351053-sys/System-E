<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'evacuation_area_id',
        'family_head_name',
        'contact_number',
        'address',
        'total_members',
        'checked_in_at',
        'checked_out_at',
        'evacuated_at',
        'status',
        'special_needs'
    ];

    protected $casts = [
    'checked_in_at' => 'datetime',
    'checked_out_at' => 'datetime',
];


    public function evacuationArea()
    {
        return $this->belongsTo(EvacuationArea::class);
    }

    /**
     * Automatically set checked_in_at when creating record with status evacuated
     */
    protected static function booted()
    {
        static::creating(function ($family) {
            if (($family->status ?? null) === 'evacuated' && empty($family->checked_in_at)) {
                $family->checked_in_at = now();
            }
        });
    }
}
