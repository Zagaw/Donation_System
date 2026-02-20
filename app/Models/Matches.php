<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matches extends Model
{
    protected $table = 'matches';
    protected $primaryKey = 'matchId';

    protected $fillable = [
        'donationId',
        'requestId',
        'interestId',
        'status',
        'matchType',
    ];

    protected $casts = [
        'execution_requested' => 'boolean',
        'completion_requested' => 'boolean',
        'execution_requested_at' => 'datetime',
        'completion_requested_at' => 'datetime'
    ];

     public function donation()
    {
        return $this->belongsTo(Donation::class, 'donationId');
    }

    public function request()
    {
        return $this->belongsTo(Request::class, 'requestId');
    }

    public function interest()
    {
        return $this->belongsTo(Interest::class, 'interestId');
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'matchId');
    }
}
