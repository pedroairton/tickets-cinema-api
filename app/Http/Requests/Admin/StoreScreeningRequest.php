<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreScreeningRequest extends FormRequest
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
            'movie_id' => ['required', 'integer', 'exists:movies,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:999.99'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
    public function messages(): array
    {
        return [
            'movie_id.required' => 'O campo filme é obrigatório.',
            'movie_id.exists' => 'O filme selecionado não existe.',
            'room_id.required' => 'O campo sala é obrigatório.',
            'room_id.exists' => 'A sala selecionada não existe.',
            'start_time.required' => 'O campo horário de início é obrigatório.',
            'start_time.after' => 'O horário de início deve ser posterior ao horário atual.',
            'end_time.required' => 'O campo horário de término é obrigatório.',
            'end_time.after' => 'O horário de término deve ser posterior ao horário de início.',
            'price.required' => 'O campo preço é obrigatório.',
        ];
    }
}
