<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === \App\Models\User::ROLE_QUEST_GIVER;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['required', 'integer', 'min:10', 'max:1000000'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Quest title is required.',
            'title.min' => 'Quest title must be at least 5 characters.',
            'title.max' => 'Quest title cannot exceed 255 characters.',
            'description.required' => 'Quest description is required.',
            'description.min' => 'Quest description must be at least 20 characters.',
            'description.max' => 'Quest description cannot exceed 5000 characters.',
            'price.required' => 'Quest price is required.',
            'price.integer' => 'Quest price must be a valid number.',
            'price.min' => 'Quest price must be at least 10 gold coins.',
            'price.max' => 'Quest price cannot exceed 1,000,000 gold coins.',
            'categories.array' => 'Categories must be an array.',
            'categories.*.exists' => 'One or more selected categories are invalid.',
            'tags.array' => 'Tags must be an array.',
            'tags.*.exists' => 'One or more selected tags are invalid.',
        ];
    }
}

