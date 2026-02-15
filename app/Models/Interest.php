<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{

    protected $primaryKey = 'interestId';
    
    protected $fillable = [
        'donorId',
        'requestId',
        'status'
    ];

    public function donor()
    {
        return $this->belongsTo(Donor::class, 'donorId');
    }

    public function request()
    {
        return $this->belongsTo(Request::class, 'requestId');
    }
}
