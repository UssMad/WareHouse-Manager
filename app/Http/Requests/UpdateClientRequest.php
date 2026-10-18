<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('client'));
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'string', 'max:40', Rule::unique('clients')->where('user_id', $this->user()->id)->ignore($this->route('client'))],
            'client_type' => ['required', Rule::in(['company', 'individual'])],
            'name' => ['required', 'string', 'max:120'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email'],
            'email' => ['nullable', 'email', 'max:120', 'required_without:phone'],
            'is_blacklisted' => ['nullable', 'boolean'],
        ];
    }
}
