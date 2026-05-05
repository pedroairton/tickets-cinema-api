<?php

namespace App\Http\Requests\Public;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MovieIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:showing,coming_soon,off_screen'],
            'genre' => ['nullable', 'string', 'exists:genres,slug'],
            'age_rating' => ['nullable', 'string', 'in:L,10,12,14,16,18'],
            'date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'duration_min' => ['nullable', 'integer', 'min:1'],
            'duration_max' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', 'string', 'in:title,release_date,duration_minutes'],
            'sort_dir' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
