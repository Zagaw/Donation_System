<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'user_id',
        'donation_id',
        'certificate_number',
        'recipient_name',
        'item_name',
        'quantity',
        'category',
        'donor_name',
        'issue_date',
        'file_path',
        'status'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'quantity' => 'integer'
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Donation
    public function donation()
    {
        return $this->belongsTo(Donation::class, 'donation_id', 'donationId');
    }
}