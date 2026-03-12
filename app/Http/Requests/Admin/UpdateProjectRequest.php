<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:projects,slug,'.$this->project->id],
            'excerpt' => ['nullable', 'string', 'max:200'],
            'content' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:10240'], // 10MB limit
            'company' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer'],
            'status' => ['required', 'string', 'in:draft,published'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'demo_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
