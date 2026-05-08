<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'synopsis' => ['required', 'string', 'max:1000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'trailer_url' => ['nullable', 'url', 'max:500'],
            'age_rating' => ['required', 'string', 'in:L,10,12,14,16,18'],
            'original_title' => ['nullable', 'string', 'max:255'],
            'director' => ['nullable', 'string', 'max:255'],
            'distributor' => ['nullable', 'string', 'max:255'],
            'country_of_origin' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:showing,coming_soon,off_screen'],
            'release_date' => ['required', 'date'],
            'genres_ids' => ['required', 'array', 'min:1'],
            'genres_ids.*' => ['required', 'integer', 'exists:genres,id'],
        ];
    }
}
