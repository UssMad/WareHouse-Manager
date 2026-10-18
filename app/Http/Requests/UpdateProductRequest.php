<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('user_id', $this->user()->id)],
            'name' => ['required', 'string', 'max:120'],
            'reference' => ['required', 'string', 'max:60', Rule::unique('products')->where('user_id', $this->user()->id)->ignore($this->route('product'))],
            'description' => ['nullable', 'string', 'max:2000'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'alert_threshold' => ['required', 'integer', 'min:0'],
        ];
    }
}
