<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    // Primary key
    protected $primaryKey = 'requestId';

    // Fillable columns
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

    // Relationship to receiver
    public function receiver()
    {
        return $this->belongsTo(Receiver::class, 'receiverId');
    }

    // Relationship to matches
    public function matches()
    {
        return $this->hasMany(DonationMatch::class);
    }
}
