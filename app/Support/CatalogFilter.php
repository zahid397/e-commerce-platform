<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final readonly class CatalogFilter
{
    public const SORTS = [
        'latest' => 'Newest first',
        'name' => 'Name A–Z',
        'price-asc' => 'Price: low to high',
        'price-desc' => 'Price: high to low',
    ];

    public function __construct(
        public string $q,
        public ?int $category,
        public ?int $subcategory,
        public string $sort,
        public string $state,
    ) {}

    public static function from(Request $request): self
    {
        $inputs = $request->query();
        $q = self::text($inputs['q'] ?? '');
        $sort = $inputs['sort'] ?? null;
        $state = $inputs['state'] ?? null;

        return new self(
            $q,
            self::id($inputs['category'] ?? null),
            self::id($inputs['subcategory'] ?? null),
            is_string($sort) && isset(self::SORTS[$sort]) ? $sort : 'latest',
            in_array($state, ['active', 'draft'], true) ? $state : '',
        );
    }

    public static function text(mixed $value): string
    {
        return is_string($value) ? mb_substr(trim($value), 0, 100) : '';
    }

    public static function id(mixed $value): ?int
    {
        return is_string($value) && preg_match('/\A[1-9]\d{0,15}\z/', $value) ? (int) $value : null;
    }

    /** Escape LIKE wildcards and always bind the value as a parameter. */
    public static function pattern(string $value): string
    {
        return '%'.strtr($value, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
    }

    public function products(Builder $query, bool $public = false): Builder
    {
        $query->when($this->q !== '', fn (Builder $q) => $q->whereRaw("name LIKE ? ESCAPE '!'", [self::pattern($this->q)]))
            ->when($this->category !== null, fn (Builder $q) => $q->where('category_id', $this->category))
            ->when($this->subcategory !== null, fn (Builder $q) => $q->where('subcategory_id', $this->subcategory));

        if ($public) {
            $query->active()->orderBy('subcategory_id');
        } elseif ($this->state !== '') {
            $query->where('is_active', $this->state === 'active');
        }

        match ($this->sort) {
            'name' => $query->orderBy('name'),
            'price-asc' => $query->orderBy('new_price'),
            'price-desc' => $query->orderByDesc('new_price'),
            default => $query->orderByDesc('created_at'),
        };

        return $query->orderByDesc('id');
    }
}
