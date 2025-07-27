<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelRequest extends Model
{
    use HasFactory;

    /**
     * Relasi ke user (staff yang mengajukan SPPD)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'travel_request_participants');
    }

    protected $fillable = [
        'kode_sppd',
        'user_id',
        'tempat_berangkat',
        'tujuan',
        'keperluan',
        'tanggal_berangkat',
        'tanggal_kembali',
        'lama_perjalanan',
        'transportasi',
        'tempat_menginap',
        'biaya_transport',
        'biaya_penginapan',
        'uang_harian',
        'biaya_lainnya',
        'total_biaya',
        'sumber_dana',
        'status',
        'current_approval_level',
        'approval_sequence',
        'approval_history',
        'catatan_pemohon',
        'catatan_approval',
        'is_urgent',
        'nomor_surat_tugas',
        'tanggal_surat_tugas',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_berangkat' => 'date',
        'tanggal_kembali' => 'date',
        'tanggal_surat_tugas' => 'date',
        'biaya_transport' => 'decimal:2',
        'biaya_penginapan' => 'decimal:2',
        'uang_harian' => 'decimal:2',
        'biaya_lainnya' => 'decimal:2',
        'total_biaya' => 'decimal:2',
        'is_urgent' => 'boolean',
        'approval_sequence' => 'array',
        'approval_history' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];


    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Accessor untuk format status
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'in_review' => 'Sedang Direview',
            'revision' => 'Revisi',
            'completed' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    /**
     * Accessor untuk format total biaya
     */
    public function getFormattedTotalBiayaAttribute(): string
    {
        if ($this->total_biaya > 0) {
            return 'Rp ' . number_format($this->total_biaya, 0, ',', '.');
        }
        return '-';
    }

    /**
     * Accessor for total_biaya that returns integer for calculations
     */
    public function getTotalBiayaAttribute($value): int
    {
        // Convert decimal/string value to integer, ensure it's not null
        return (int) ($value ?? 0);
    }

    /**
     * Accessor for total_budget (dynamic sum of all biaya fields)
     */
    public function getTotalBudgetAttribute()
    {
        // Pastikan semua komponen biaya dikonversi ke integer
        $transport = (int) ($this->biaya_transport ?? 0);
        $penginapan = (int) ($this->biaya_penginapan ?? 0);
        $harian = (int) ($this->uang_harian ?? 0);
        $lainnya = (int) ($this->biaya_lainnya ?? 0);
        
        // Log untuk debugging
        \Log::debug('TravelRequest ID: ' . $this->id . ' - Budget Components', [
            'biaya_transport' => $transport,
            'biaya_penginapan' => $penginapan,
            'uang_harian' => $harian,
            'biaya_lainnya' => $lainnya,
            'total' => $transport + $penginapan + $harian + $lainnya
        ]);
        
        return $transport + $penginapan + $harian + $lainnya;
    }

    /**
     * Accessor untuk badge urgent
     */
    public function getUrgencyBadgeAttribute(): string
    {
        return $this->is_urgent ? 'URGENT' : '';
    }

    /**
     * Get approval flow for this travel request based on submitter role (dinamis)
     */
    public function getApprovalFlow(): array
    {
        $service = app(\App\Services\ApprovalService::class);
        return $service->getApprovalFlowForSubmitter($this->user->role);
    }

    /**
     * Get current approver role based on approval level (dinamis)
     */
    public function getCurrentApproverRoleAttribute(): ?string
    {
        if ($this->status !== 'in_review' || $this->current_approval_level <= 0) {
            return null;
        }
        $flow = $this->getApprovalFlow();
        return $flow[$this->current_approval_level] ?? null;
    }

    /**
     * Set current approval level based on role
     */
    public function setCurrentApprovalLevelByRole(string $role): void
    {
        $flow = $this->getApprovalFlow();
        $level = array_search($role, $flow);

        if ($level !== false) {
            $this->current_approval_level = $level + 1;
        }
    }

    /**
     * Calculate duration of travel in days
     */
    public function calculateDuration(): int
    {
        if (!$this->tanggal_berangkat || !$this->tanggal_kembali) {
            return 0;
        }

        return $this->tanggal_berangkat->diffInDays($this->tanggal_kembali) + 1;
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by pending status
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for filtering by completed status
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for filtering by in review status
     */
    public function scopeInReview($query)
    {
        return $query->where('status', 'in_review');
    }

    /**
     * Calculate and set total biaya when component costs change
     */
    public function calculateTotalBiaya(): void
    {
        // Pastikan semua komponen biaya dikonversi ke integer
        $transport = (int) ($this->biaya_transport ?? 0);
        $penginapan = (int) ($this->biaya_penginapan ?? 0);
        $harian = (int) ($this->uang_harian ?? 0);
        $lainnya = (int) ($this->biaya_lainnya ?? 0);
        
        // Hitung total dan log untuk debugging
        $total = $transport + $penginapan + $harian + $lainnya;
        
        \Log::debug('TravelRequest calculateTotalBiaya ID: ' . $this->id, [
            'biaya_transport' => $transport,
            'biaya_penginapan' => $penginapan,
            'uang_harian' => $harian,
            'biaya_lainnya' => $lainnya,
            'total_biaya' => $total
        ]);
        
        $this->total_biaya = $total;
    }

    /**
     * Boot method to automatically calculate total when saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto calculate total biaya if component costs are set
            if ($model->isDirty(['biaya_transport', 'biaya_penginapan', 'uang_harian', 'biaya_lainnya'])) {
                $model->calculateTotalBiaya();
            }
        });
    }
}
