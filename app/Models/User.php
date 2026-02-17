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

    public function notifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc');
    }
}
