<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $table = 'donations';
    protected $primaryKey = 'donationId';
    
    protected $fillable = [
        'donorId',
        'itemName',
        'category',
        'quantity',
        'description',
        'status'
    ];

    // Relationship with User (if applicable)
    public function user()
    {
        return $this->belongsTo(User::class, 'donorId', 'id');
    }

    // Relationship with Donor (if you have Donor model)
    public function donor()
    {
        return $this->belongsTo(Donor::class, 'donorId', 'id');
    }

    // Relationship with Certificate
    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'donation_id', 'donationId');
    }
}