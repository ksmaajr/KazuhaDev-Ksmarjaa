<?php

namespace App\Http\Requests;

use App\Support\LicenseDuration;
use Illuminate\Foundation\Http\FormRequest;

class ExtendLicenseRequest extends FormRequest
{
    protected $redirectRoute = 'licenses';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return LicenseDuration::rules($this->all());
    }
}
