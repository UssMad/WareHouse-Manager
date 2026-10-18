<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['entry', 'exit'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'client_id' => ['nullable', 'integer', 'required_if:type,exit', 'required_if:is_return,1', Rule::exists('clients', 'id')->where('user_id', $this->user()->id)],
            'is_return' => ['nullable', 'boolean'],
            'supplier' => ['nullable', 'string', 'max:120'],
            'reason' => ['nullable', 'string', 'max:160'],
            'note' => ['nullable', 'string', 'max:1000'],
            'occurred_at' => ['required', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->has('client_id') || ! $this->filled('client_id')) {
                return;
            }

            $client = Client::where('user_id', $this->user()->id)->find($this->integer('client_id'));

            if ($this->input('type') === 'exit' && $client?->is_blacklisted) {
                $validator->errors()->add('client_id', 'Ce client est blacklisté. Une sortie de stock ne peut pas lui être enregistrée.');
            }
        });
    }
}
