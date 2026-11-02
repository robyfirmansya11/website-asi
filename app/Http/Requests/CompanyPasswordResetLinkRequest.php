<?php

namespace App\Http\Requests;

use Laravel\Fortify\Http\Requests\SendPasswordResetLinkRequest;

class CompanyPasswordResetLinkRequest extends SendPasswordResetLinkRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['email' => ['required', 'string', 'email', 'max:254', 'ends_with:@artabumisentraindustri.com']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['email.ends_with' => 'Gunakan email perusahaan @artabumisentraindustri.com.'];
    }
}
