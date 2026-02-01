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
        'status'
    ];

    public function donor()
    {
        return $this->belongsTo(Donor::class, 'donorId');
    }
}

