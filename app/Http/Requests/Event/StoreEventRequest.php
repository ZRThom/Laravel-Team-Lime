<?php

namespace App\Http\Requests\Event;

use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        // the organization must be one the user owns (an admin can pick any)
        $organizationRule = $user->is_admin
            ? Rule::exists('organizations', 'id')
            : Rule::exists('organization_user', 'organization_id')
                ->where('user_id', $user->id)
                ->where('role', 'owner');

        return [
            'organization_id' => ['required', 'integer', $organizationRule],
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
