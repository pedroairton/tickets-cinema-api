<?php

namespace App\Http\Requests\Public;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ScreeningIndexRequest extends FormRequest
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
            'date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'movie_id' => ['nullable', 'integer', 'exists:movies,id'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
