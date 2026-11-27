<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialTransactionRequest extends FormRequest
{
    protected $redirectRoute = 'finance.index';

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return ['direction' => ['required', 'in:income,expense'], 'amount' => ['required', 'integer', 'min:1', 'max:100000000000'], 'fee' => ['required', 'integer', 'min:0', 'lte:amount'], 'description' => ['required', 'string', 'max:255'], 'paid_at' => ['required', 'date_format:Y-m-d\TH:i'], 'external_reference' => ['nullable', 'string', 'max:100', 'unique:financial_transactions,external_reference'], 'source' => ['prohibited'], 'order_id' => ['prohibited']];
    }
}
