<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:50', 
            Rule::unique('rooms', 'name')->ignore($this->route('room'))],
            'total_rows' => ['sometimes', 'required', 'integer', 'min:1', 'max:30'],
            'total_columns' => ['sometimes', 'required', 'integer', 'min:1', 'max:40'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
