<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:posts,slug'],
            'excerpt' => ['nullable', 'string', 'max:200'],
            'content' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:draft,published'],
            'post_serie_id' => ['nullable', 'integer', 'exists:post_series,id'],
            'image_uuids' => ['nullable', 'array'],
            'image_uuids.*' => ['string', 'exists:files,uuid'],
        ];
    }
}
