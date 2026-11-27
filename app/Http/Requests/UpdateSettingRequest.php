<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    protected $redirectRoute = 'settings';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'public_url' => ['required', 'url:http,https', 'max:255', function ($attribute, $value, $fail): void {
                $parts = parse_url($value);
                if (isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment']) || ! in_array($parts['path'] ?? '', ['', '/'])) {
                    $fail('Gunakan origin server saja, tanpa path, kredensial, query, atau fragment.');
                }
            }],
            'whatsapp_number' => ['nullable', 'string', 'regex:/^[1-9][0-9]{7,14}$/'],
            'heartbeat_interval' => ['required', 'integer', 'between:30,300'],
        ];
    }
}
