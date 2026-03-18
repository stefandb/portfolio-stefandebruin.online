<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Tags\Tag;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string|callable>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, callable $fail): void {
                    if (Tag::whereRaw("name->>'en' = ?", [$value])->exists()) {
                        $fail('Een tag met deze naam bestaat al.');
                    }
                },
            ],
            'type' => ['nullable', 'string', 'max:255'],
        ];
    }
}
