<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_request_id',
        'approver_id',
        'level',
        'role',
        'status',
        'comments',
        'revision_notes',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'revision_notes' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Relasi ke pengajuan SPPD (TravelRequest)
     */
    public function travelRequest()
    {
        return $this->belongsTo(TravelRequest::class);
    }

    /**
     * Relasi ke user yang menyetujui (approver)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Alias relasi user() untuk kompatibilitas kode lama
     * Mengarah ke approver_id
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
