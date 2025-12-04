<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Earthquake extends Model
{
    use HasFactory;
    protected $fillable = [
        "date",
        "total_earthquakes",
        "avg_magnitude",
        "max_magnitude",
    ] ;
}
