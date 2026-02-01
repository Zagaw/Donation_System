<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    protected $fillable = ['userId', 'donorType'];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }
}
