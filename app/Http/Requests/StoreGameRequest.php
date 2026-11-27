<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
{
    protected $redirectRoute = 'games.index';

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:games,code'],
            'banner_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=4000'],
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=2400,max_height=2400'],
            'release_version' => ['nullable', 'string', 'max:50'],
            'injector_file' => ['nullable', 'file', 'max:20480', 'extensions:exe,zip'],
            'dll_file' => ['nullable', 'file', 'max:20480', 'extensions:dll'],
            'dll_variants' => ['sometimes', 'array', 'max:20'],
            'dll_variants.*' => ['array:code,name,file,remove'],
            'dll_variants.*.code' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'distinct:strict'],
            'dll_variants.*.name' => ['required', 'string', 'max:100'],
            'dll_variants.*.file' => ['nullable', 'file', 'max:20480', 'extensions:dll'],
            'dll_variants.*.remove' => ['sometimes', 'boolean'],
        ];
    }
}
