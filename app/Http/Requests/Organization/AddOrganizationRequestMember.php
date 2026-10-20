<?php

namespace App\Http\Requests\Organization;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddOrganizationRequestMember extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organization = $this->route('organization'); // catch organization in url when route model bind
        // check if organization owner is link with user id, then valid request
        return $organization instanceof Organization && this->user() !== null && $organization->users()->where('user.id', $this->user()->id)->wherePivot('role', 'owner')->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:user_id'], // assign user, assign integer (value), ID have to exist in user table (id)
        ];
    }
}
