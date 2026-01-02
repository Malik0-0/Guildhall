<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteQuestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $quest = $this->route('id') 
            ? \App\Models\Quest::findOrFail($this->route('id'))
            : null;

        return $quest && 
               auth()->check() && 
               $quest->adventurer_id === auth()->id() &&
               $quest->canBeCompleted();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'evidence' => ['required', 'string', 'min:20', 'max:10000'],
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
            'evidence.required' => 'Completion evidence is required.',
            'evidence.min' => 'Evidence must be at least 20 characters. Please provide more details.',
            'evidence.max' => 'Evidence cannot exceed 10,000 characters.',
        ];
    }
}

