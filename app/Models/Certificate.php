<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $primaryKey = 'certificateId';

    protected $fillable = [
        'matchId',
        'donorId',
        'certificateNumber',
        'title',
        'description',
        'itemName',
        'quantity',
        'category',
        'recipientName',
        'issueDate',
        'filePath',
        'status'
    ];

    protected $casts = [
        'issueDate' => 'date'
    ];

    public function match()
    {
        return $this->belongsTo(Matches::class, 'matchId');
    }

    public function donor()
    {
        return $this->belongsTo(Donor::class, 'donorId');
    }

    // Generate unique certificate number
    public static function generateCertificateNumber()
    {
        $prefix = 'CERT';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(uniqid(), -6));
        
        return "{$prefix}-{$year}{$month}-{$random}";
    }
}