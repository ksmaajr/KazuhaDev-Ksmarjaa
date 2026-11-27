<?php

namespace App\Http\Requests;

class UpdateGameRequest extends StoreGameRequest
{
    public function rules(): array
    {
        return array_replace(parent::rules(), ['code' => ['prohibited'], 'remove_injector' => ['sometimes', 'boolean'], 'remove_dll' => ['sometimes', 'boolean'], 'remove_banner' => ['sometimes', 'boolean'], 'remove_logo' => ['sometimes', 'boolean']]);
    }
}
