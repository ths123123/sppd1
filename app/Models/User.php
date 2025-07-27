<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'nip',
        'jabatan',
        'role',
        'phone',
        'address',
        'pangkat',
        'golongan',
        'unit_kerja',
        'is_active',
        'last_login_at',
        'email_verified_at',
        'remember_token',
        // Profile fields
        'avatar',
        'bio',
        'department',
        'employee_id',
        'birth_date',
        'gender',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'birth_date' => 'date',
        'settings' => 'array',
    ];

    /**
     * Automatically hash the password when it is set.
     */
    public function setPasswordAttribute($password)
    {
        // Only hash if password is not already hashed
        if ($password && !preg_match('/^\$2y\$/', $password)) {
            $this->attributes['password'] = bcrypt($password);
        } else {
            $this->attributes['password'] = $password;
        }
    }

    /**
     * Get the avatar URL attribute.
     */
    public function getAvatarUrlAttribute()
    {
        try {
            if ($this->avatar) {
                $path = $this->avatar;
                
                // Jika path tidak dimulai dengan 'avatars/', tambahkan prefix
                if (!str_starts_with($path, 'avatars/')) {
                    $path = 'avatars/' . $path;
                }
                
                // Periksa apakah file ada
                if (Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path) . '?v=' . time();
                } else {
                    // Log file tidak ditemukan
                    Log::warning('Avatar file not found', [
                        'user_id' => $this->id,
                        'avatar_path' => $path
                    ]);
                }
            }
            
            // Return default avatar based on name initial
            $initial = strtoupper(substr($this->name, 0, 1));
            return "https://ui-avatars.com/api/?name={$initial}&background=6366f1&color=ffffff&size=200";
        } catch (\Exception $e) {
            // Log error
            Log::error('Error getting avatar URL', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            // Return default avatar
            $initial = strtoupper(substr($this->name, 0, 1));
            return "https://ui-avatars.com/api/?name={$initial}&background=6366f1&color=ffffff&size=200";
        }
    }

    /**
     * Get user's phone number formatted for WhatsApp
     */
    public function getWhatsappNumberAttribute()
    {
        if (!$this->phone) {
            return null;
        }

        // Remove any non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $this->phone);

        // If starts with 0, replace with +62
        if (substr($phone, 0, 1) === '0') {
            $phone = '+62' . substr($phone, 1);
        }

        // If doesn't start with +, assume it's Indonesian number
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+62' . $phone;
        }

        return $phone;
    }

    /**
     * Get the travel requests for the user.
     */
    public function travelRequests()
    {
        return $this->hasMany(\App\Models\TravelRequest::class);
    }

    /**
     * Get the travel requests where the user is a participant.
     */
    public function travelRequestsAsParticipant()
    {
        return $this->belongsToMany(TravelRequest::class, 'travel_request_participants');
    }

    /**
     * Check if user is admin (can manage users).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'kasubbag', 'sekretaris', 'ppk']);
    }

    /**
     * Check if user can approve travel requests.
     */
    public function canApprove(): bool
    {
        return in_array($this->role, ['admin', 'kasubbag', 'sekretaris', 'ppk']);
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by role.
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Get the full name attribute (accessor).
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get role display name.
     */
    public function getRoleDisplayName(): string
    {
        $roleNames = [
            'admin' => 'Admin',
            'kasubbag' => 'Kepala Sub Bagian',
            'sekretaris' => 'Sekretaris',
            'ppk' => 'PPK',
            'staff' => 'Staff',
        ];

        return $roleNames[$this->role] ?? $this->role;
    }








}
