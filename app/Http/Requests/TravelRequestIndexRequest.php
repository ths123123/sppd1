<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TravelRequestIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['kasubbag', 'sekretaris', 'ppk', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'nullable|string|in:in_review,completed,rejected,revision',
            'user_role' => 'nullable|string|in:staff,kasubbag,sekretaris,ppk',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2020',
            'destination' => 'nullable|string|max:255',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort_by' => 'nullable|string|in:created_at,tanggal_berangkat,total_biaya',
            'sort_order' => 'nullable|string|in:asc,desc',
        ];
    }
}
