<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $primaryKey = 'feedbackId';

    protected $fillable = [
        'userId',
        'userRole',
        'matchId',
        'rating',
        'comment',
        'category',
        'is_anonymous',
        'status',
        'admin_response',
        'responded_at'
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'responded_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function match()
    {
        return $this->belongsTo(Matches::class, 'matchId');
    }

    // Get display name based on anonymity
    public function getDisplayNameAttribute()
    {
        if ($this->is_anonymous) {
            return 'Anonymous ' . ucfirst($this->userRole);
        }
        return $this->user->name;
    }

    // Get rating stars as array
    public function getRatingStarsAttribute()
    {
        $stars = [];
        for ($i = 1; $i <= 5; $i++) {
            $stars[] = [
                'filled' => $i <= $this->rating,
                'value' => $i
            ];
        }
        return $stars;
    }
}