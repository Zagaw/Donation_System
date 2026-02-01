<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receiver extends Model
{
    protected $fillable = ['userId', 'receiverType'];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }
}

