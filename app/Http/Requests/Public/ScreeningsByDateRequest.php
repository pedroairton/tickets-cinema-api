<?php

namespace App\Http\Requests\Public;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ScreeningsByDateRequest extends FormRequest
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
            'date' => ['required', 'date', 'date_format:Y-m-d'],
        ];
    }

    public function messages(){
        return [
            'date.required' => 'A data é́ obrigatória.',
            'date.date' => 'Informe uma data válida.',
            'date.date_format' => 'A data deve estar no formato AAAA-MM-DD.',
        ];
    }
}
