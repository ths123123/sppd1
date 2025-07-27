<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TravelRequestStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->role === 'kasubbag';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'tempat_berangkat' => 'required|string|max:255',
            'tujuan' => 'required|string|max:255',
            'keperluan' => 'required|string',
            'tanggal_berangkat' => 'required|date|after_or_equal:today',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_berangkat',
            'transportasi' => 'required|string|max:255',
            'tempat_menginap' => 'nullable|string|max:255',
            'biaya_transport' => 'nullable|numeric|min:0',
            'biaya_penginapan' => 'nullable|numeric|min:0',
            'uang_harian' => 'nullable|numeric|min:0',
            'biaya_lainnya' => 'nullable|numeric|min:0',
            'total_biaya' => 'nullable|numeric|min:0',
            'sumber_dana' => 'nullable|string|max:255',
            'catatan_pemohon' => 'nullable|string',
            'is_urgent' => 'nullable|boolean',
            'action' => 'nullable|string|in:save,submit',
            'participants' => 'nullable|array',
            'participants.*' => 'integer|exists:users,id',
            'dokumen_pendukung.*' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048',
                function ($attribute, $value, $fail) {
                    $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
                    if (!in_array($value->getMimeType(), $allowedMimes)) {
                        $fail('File type tidak diizinkan.');
                    }
                }
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'dokumen_pendukung.*.max' => 'Ukuran file maksimal 2MB.',
            'dokumen_pendukung.*.mimes' => 'Format file harus PDF, JPG, atau PNG.',
            'tanggal_berangkat.after_or_equal' => 'Tanggal berangkat harus hari ini atau setelahnya.',
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali harus sama dengan atau setelah tanggal berangkat.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tempat_berangkat' => 'tempat berangkat',
            'tanggal_berangkat' => 'tanggal berangkat',
            'tanggal_kembali' => 'tanggal kembali',
            'biaya_transport' => 'biaya transport',
            'biaya_penginapan' => 'biaya penginapan',
            'uang_harian' => 'uang harian',
            'biaya_lainnya' => 'biaya lainnya',
            'dokumen_pendukung' => 'dokumen pendukung',
        ];
    }
} 