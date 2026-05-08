<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'screening_id' => ['required', 'integer', 'exists:screenings,id'],
            'seat_ids' => ['required', 'array', 'min:1', 'max:10'],
            'seat_ids.*' => ['required', 'integer', 'exists:seats,id'],
            'payment_method' => ['required', 'string', 'in:credit_card,debit_card,pix'],
        ];
    }
    public function messages(){
        return [
            'seat_ids.max' => 'Você pode selecionar no máximo 10 assentos por pedido.'
        ];
    }
}
