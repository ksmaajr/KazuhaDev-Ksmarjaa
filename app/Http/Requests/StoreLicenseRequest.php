<?php

namespace App\Http\Requests;

use App\Models\Game;
use App\Models\LicenseProduct;
use App\Support\LicenseDuration;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class StoreLicenseRequest extends FormRequest
{
    protected $redirectRoute = 'licenses';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('product_id')) {
            return;
        }

        $id = $this->input('product_id');
        if (! is_scalar($id) || ! filter_var($id, FILTER_VALIDATE_INT)) {
            throw ValidationException::withMessages(['product_id' => 'Paket tidak valid.']);
        }

        $product = LicenseProduct::where('is_active', true)->find($id);
        if (! $product) {
            throw ValidationException::withMessages(['product_id' => 'Paket tidak tersedia.']);
        }

        $seconds = $product->duration_seconds ?? $product->days * 86400;
        $ids = array_values(array_unique(array_map('intval', $product->game_ids ?? [])));
        if ($seconds < 60 || $seconds > 2592000 || $seconds % 60 !== 0 || ! $ids || Game::whereIn('id', $ids)->count() !== count($ids)) {
            throw ValidationException::withMessages(['product_id' => 'Konfigurasi paket tidak valid. Periksa durasi dan game.']);
        }

        $this->offsetUnset('days');
        $this->merge([
            'hours' => intdiv($seconds, 3600),
            'minutes' => intdiv($seconds % 3600, 60),
            'game_ids' => $ids,
        ]);
    }

    public function rules(): array
    {
        return ['member_name' => ['required', 'string', 'max:100'], 'game_ids' => ['required', 'array', 'min:1', 'max:100'], 'game_ids.*' => ['required', 'integer', 'distinct', 'exists:games,id'], 'label' => ['required', 'string', 'max:100'], 'notes' => ['nullable', 'string', 'max:1000']] + LicenseDuration::rules($this->all());
    }
}
