<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemplateDokumen extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_template',
        'path_file',
        'tipe_file',
        'status_aktif',
        'jenis_template',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
