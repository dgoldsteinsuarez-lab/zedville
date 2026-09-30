<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255'],
            // Same rule as registration: single word, 3-15 chars, no family name.
            'nickname' => ['nullable', 'string', 'min:3', 'max:15', 'regex:/^\S+$/'],
            'email' => ['email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
             'age' => ['nullable', 'string', 'min:0', 'max:255'],
             'grade' => ['nullable', 'string', 'min:0', 'max:255'],
             'address' => ['nullable', 'string', 'min:0', 'max:255'],
             'mascot' => ['nullable', 'exists:mascots,id'],
        ];
    }
}
