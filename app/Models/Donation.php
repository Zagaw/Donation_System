<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $primaryKey = 'donationId';

    protected $fillable = [
        'donorId',
        'itemName',
        'category',
        'quantity',
        'description',
        'status',
        'nrcNumber',
        'nrcFrontImage',
        'nrcBackImage'
    ];

    protected $appends = [
        'nrc_front_url',
        'nrc_back_url'
    ];

    public function donor()
    {
        return $this->belongsTo(Donor::class, 'donorId');
    }

    public function matches()
    {
        return $this->hasMany(Matches::class, 'donationId');
    }

    // Accessor for NRC Front Image
    public function getNrcFrontUrlAttribute()
    {
        return $this->nrcFrontImage 
            ? asset('storage/' . $this->nrcFrontImage) 
            : null;
    }

    // Accessor for NRC Back Image
    public function getNrcBackUrlAttribute()
    {
        return $this->nrcBackImage 
            ? asset('storage/' . $this->nrcBackImage) 
            : null;
    }
}

