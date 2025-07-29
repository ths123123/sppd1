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
        $user = auth()->user();
        return in_array($user->role, ['kasubbag', 'admin']);
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
            'participants' => 'nullable',
            'participants_hidden' => 'nullable|string',
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

    /**
     * Prepare the data for validation.
     * This method handles all possible formats of participants data to prevent recurring bugs.
     */
    protected function prepareForValidation()
    {
        $participants = $this->input('participants');
        
        // Handle various formats of participants data - ROBUST SOLUTION
        if ($participants) {
            $processedParticipants = $this->normalizeParticipantsData($participants);
            $this->merge(['participants' => $processedParticipants]);
        }
    }

    /**
     * Normalize participants data from any format to array of valid user IDs
     * This prevents all recurring bugs related to participants data format
     */
    private function normalizeParticipantsData($participants): array
    {
        $result = [];
        
        if (is_string($participants)) {
            // Handle comma-separated string: "1,2,3" or "1, 2, 3"
            if (str_contains($participants, ',')) {
                $result = array_filter(
                    array_map('trim', explode(',', $participants)),
                    function($id) { return !empty($id) && is_numeric($id); }
                );
            } else {
                // Single value: "1"
                if (!empty($participants) && is_numeric($participants)) {
                    $result = [$participants];
                }
            }
        } elseif (is_array($participants)) {
            foreach ($participants as $participant) {
                if (is_string($participant)) {
                    if (str_contains($participant, ',')) {
                        // Handle array with comma strings: ["1,2,3"]
                        $split = array_filter(
                            array_map('trim', explode(',', $participant)),
                            function($id) { return !empty($id) && is_numeric($id); }
                        );
                        $result = array_merge($result, $split);
                    } else {
                        // Handle normal array: ["1", "2", "3"]
                        if (!empty($participant) && is_numeric($participant)) {
                            $result[] = $participant;
                        }
                    }
                } elseif (is_numeric($participant)) {
                    // Handle numeric values: [1, 2, 3]
                    $result[] = (string)$participant;
                }
            }
        }
        
        // Remove duplicates and ensure all are strings
        return array_unique(array_map('strval', $result));
    }

    /**
     * Custom validation for participants after normalization
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $participants = $this->input('participants');
            
            if ($participants && is_array($participants)) {
                foreach ($participants as $index => $participantId) {
                    // Validate each participant ID
                    if (!is_numeric($participantId)) {
                        $validator->errors()->add("participants.{$index}", 'ID peserta harus berupa angka.');
                        continue;
                    }
                    
                    // Check if user exists and is not admin
                    $user = \App\Models\User::find($participantId);
                    if (!$user) {
                        $validator->errors()->add("participants.{$index}", 'Peserta tidak ditemukan.');
                    } elseif ($user->role === 'admin') {
                        $validator->errors()->add("participants.{$index}", 'Admin tidak dapat dipilih sebagai peserta.');
                    }
                }
            }
        });
    }
} 