<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_request_id',
        'uploaded_by',
        'filename',
        'original_filename',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'document_type',
        'description',
        'is_required',
        'is_verified',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // Relasi ke travel request
    public function travelRequest()
    {
        return $this->belongsTo(TravelRequest::class);
    }

    // Relasi ke user yang mengunggah
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Relasi ke user yang memverifikasi (jika ada)
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
