<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'userId';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role'
    ];

    protected $hidden = ['password'];

    public function donor()
    {
        return $this->hasOne(Donor::class, 'userId');
    }

    public function receiver()
    {
        return $this->hasOne(Receiver::class, 'userId');
    }

    public function donatedMatches()
    {
    return $this->hasMany(DonationMatch::class, 'donor_id');
    }

    public function receivedMatches()
    {
    return $this->hasMany(DonationMatch::class, 'receiver_id');
    }

    public function donations()
    {
    return $this->hasMany(Donation::class, 'donorId');
    }

    public function requests() 
    {
    return $this->hasMany(Request::class, 'receiverId');
    }

}
