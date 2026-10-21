<?php

namespace App\Http\Requests\Event;

use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $event = $this->route('event'); // get by url with route model binding

        return $event instanceof Event && ($this->user()?->can('update', $event) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sport_id' => ['required', 'integer', 'exists:sports,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'between:30,240'],
            'city' => ['required', 'string', 'max:255'],
            'venue' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'between:2,100'],
        ];
    }
}
