<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class RegisterParticipantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // any connected user can participate
        return $this->user()?->can('registration.create', $this->route('event')) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
