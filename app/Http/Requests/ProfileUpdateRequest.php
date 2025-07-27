<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = optional(auth()->user())->id;
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($userId),
            ],
            'nip' => [
                'nullable', 
                'string', 
                'max:18', 
                'regex:/^[0-9]{18}$/', // NIP format: 18 digit angka (YYYYMMDDNNNNNNNNN)
                Rule::unique(User::class)->ignore($userId)
            ],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'unit_kerja' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s]+$/'],
            'pangkat' => ['nullable', 'string', 'max:100'],
            'golongan' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:500'],
            'department' => ['nullable', 'string', 'max:100'],
            'employee_id' => ['nullable', 'string', 'max:20', Rule::unique(User::class)->ignore($userId)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female'],
        ];
    }

    /**
     * Get custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'nip.regex' => 'NIP harus berupa 18 digit angka sesuai format pemerintah Indonesia (YYYYMMDDNNNNNNNNN)',
            'nip.max' => 'NIP maksimal 18 digit angka',
        ];
    }
}
