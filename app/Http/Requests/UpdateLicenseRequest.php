<?php

namespace App\Http\Requests;

use App\Support\LicenseDuration;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLicenseRequest extends FormRequest
{
    protected $redirectRoute = 'licenses';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['member_name' => ['sometimes', 'string', 'max:100'], 'game_ids' => ['sometimes', 'array', 'min:1', 'max:100'], 'game_ids.*' => ['required', 'integer', 'distinct', 'exists:games,id'], 'label' => ['required', 'string', 'max:100'], 'notes' => ['nullable', 'string', 'max:1000']] + LicenseDuration::rules($this->all(), false);
    }
}
