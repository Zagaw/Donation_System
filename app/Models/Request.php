<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $primaryKey = 'requestId';

    protected $fillable = [
        'receiverId',
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

    public function receiver()
    {
        return $this->belongsTo(Receiver::class, 'receiverId');
    }

    public function interests()
    {
        return $this->hasMany(Interest::class, 'requestId');
    }

    public function matches()
    {
        return $this->hasMany(Matches::class, 'requestId');
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

