<?php

namespace App\Models;

use App\Support\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['reference', 'checkout_token', 'name', 'email', 'phone', 'address', 'status', 'total_amount'];

    protected $hidden = ['checkout_token'];

    protected function casts(): array
    {
        return ['status' => OrderStatus::class, 'total_amount' => 'decimal:2'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
