<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Tags\Tag;

class UpdateTagRequest extends FormRequest
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
        /** @var Tag|string|null $tag */
        $tag = $this->route('tag');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, callable $fail) use ($tag): void {
                    if ($tag instanceof Tag && Tag::whereRaw("name->>'en' = ?", [$value])->where('id', '!=', $tag->id)->exists()) {
                        $fail('Een tag met deze naam bestaat al.');
                    }
                },
            ],
            'type' => ['nullable', 'string', 'max:255'],
        ];
    }
}
