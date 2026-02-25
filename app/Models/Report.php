<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $primaryKey = 'reportId';

    protected $fillable = [
        'title',
        'type',
        'format',
        'file_path',
        'file_size',
        'date_range',
        'generated_at',
        'generated_by'
    ];

    protected $casts = [
        'generated_at' => 'datetime'
    ];

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Helper to get file size in human readable format
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 1) . ' ' . $units[$i];
    }
}