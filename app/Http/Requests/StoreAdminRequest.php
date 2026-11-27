<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreAdminRequest extends FormRequest
{
    protected $redirectRoute = 'admins.index';

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', 'string', 'confirmed', function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value) && (strlen($value) > 72 || str_contains($value, "\0"))) {
                $fail('Password maksimal 72 byte dan tidak boleh mengandung karakter null.');
            }
        }, Password::min(12)->mixedCase()->numbers()], 'role' => ['prohibited']];
    }
}
