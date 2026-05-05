<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScreeningRequest extends FormRequest
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
            'movie_id' => ['sometimes', 'required', 'integer', 'exists:movies,id'],
            'room_id' => ['sometimes', 'required', 'integer', 'exists:rooms,id'],
            'start_time' => ['sometimes', 'required', 'date', 'after:now'],
            'end_time' => ['sometimes', 'required', 'date', 'after:start_time'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0.01', 'max:999.99'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
