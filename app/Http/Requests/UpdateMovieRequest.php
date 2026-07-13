<?php

namespace App\Http\Requests;

use App\Models\Movie;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateMovieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('title')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Movie|null $movie */
        $movie = $this->route('movie');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:movies,slug'.($movie !== null ? ','.$movie->id : ''),
            ],
            'description' => ['required', 'string'],
            'genre' => ['required', 'string', 'max:255'],
            'duration' => ['required', 'integer', 'min:1', 'max:1000'],
            'release_date' => ['required', 'date'],
            'poster' => ['required', 'string', 'max:255'],
            'trailer_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
