<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'travel_request_id',
        'title',
        'message',
        'type',
        'data',
        'action_url',
        'action_text',
        'is_read',
        'read_at',
        'is_important',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_important' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Relasi ke pengguna yang menerima notifikasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi opsional ke travel request terkait
    public function travelRequest()
    {
        return $this->belongsTo(TravelRequest::class);
    }
}
