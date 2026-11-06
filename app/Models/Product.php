<?php

namespace App\Models;

use App\Services\ImageUploadService;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'subcategory_id', 'name', 'slug', 'description', 'image', 'old_price', 'new_price', 'is_active',
    ];

    protected function casts(): array
    {
        return ['old_price' => 'decimal:2', 'new_price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function imageUrl(): string
    {
        return ImageUploadService::publicUrl($this->image);
    }

    public function discountPercent(): int
    {
        $old = Money::minor($this->old_price ?? '0.00');
        $new = Money::minor($this->new_price);

        return $old > $new ? intdiv(($old - $new) * 100, $old) : 0;
    }
}
