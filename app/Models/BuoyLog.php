<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuoyLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'buoy_id',
        'lat',
        'lng',
        'status',
        'radius',
        'logged_at'
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function buoy()
    {
        return $this->belongsTo(Buoy::class);
    }
}
