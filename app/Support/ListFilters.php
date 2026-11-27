<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ListFilters
{
    public static function read(Request $request, array $extra = []): array
    {
        return $request->validate($extra + [
            'q' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])],
            'per_page' => ['nullable', 'integer', 'in:8,10,25,50'],
            'sort' => ['nullable', 'in:newest,oldest,name,amount_desc,amount_asc'],
        ]);
    }

    public static function search(Builder $query, array $filters, array $columns): void
    {
        if ($search = $filters['q'] ?? null) {
            $query->where(function ($query) use ($search, $columns): void {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }
    }

    public static function dates(Builder $query, array $filters, string $column = 'created_at'): void
    {
        if ($filters['from'] ?? null) {
            $query->where($column, '>=', CarbonImmutable::parse($filters['from'], 'Asia/Jakarta')->startOfDay()->utc());
        }
        if ($filters['to'] ?? null) {
            $query->where($column, '<=', CarbonImmutable::parse($filters['to'], 'Asia/Jakarta')->endOfDay()->utc());
        }
    }

    public static function sort(Builder $query, array $filters, string $name = 'name', ?string $amount = null): void
    {
        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('id'),
            'name' => $query->orderBy($name)->orderByDesc('id'),
            'amount_desc' => $query->orderByDesc($amount ?? 'id')->orderByDesc('id'),
            'amount_asc' => $query->orderBy($amount ?? 'id')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };
    }
}
