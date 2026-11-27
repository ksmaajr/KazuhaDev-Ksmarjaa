<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MemberLoginRequest extends FormRequest
{
    protected $redirectRoute = 'member.login';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['key' => ['required', 'string', 'max:80']];
    }
}
