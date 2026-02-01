<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Donation;

class Donor extends Model
{
    protected $fillable = ['userId', 'donorType'];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'donorId');
    }
}
