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
        'status'
    ];
}
