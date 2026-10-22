<?php

namespace App\Http\Requests\Organization;

use App\Models\Organization;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RemoveOrganizationMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organization = $this->route('organization'); // catch organization in url when route model bind

        return $organization instanceof Organization && ($this->user()?->can('update', $organization) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $organization = $this->route('organization');
        return [
            'user_id' => [
                'required', 'integer', Rule::exists('organization_user', 'user_id')->where('organization_id', $organization->id)->where('role', 'member'),
            ],
            // check if user is here, then if he is in a organization, and avoid del owner
        ];
    }
    //give the id to modify the form request, if not, get the id in url with route model binding
    protected function prepareForValidation(): void
    {
    $this->merge([
        'user_id' => $this->route('user')?->id ?? $this->route('user'),
    ]);
}
}
