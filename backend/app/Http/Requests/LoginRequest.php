<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function credentials(): array
    {
        return [
            'email' => $this->string(key: 'email')->toString(),
            'password' => $this->string(key: 'password')->toString(),
        ];
    }
}
