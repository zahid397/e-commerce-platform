<?php

namespace App\Services;

use App\Models\Order;
use App\Support\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class OrderService
{
    public function place(Cart $cart, array $fields, string $reviewedHash): Order
    {
        return DB::transaction(function () use ($cart, $fields, $reviewedHash): Order {
            $quote = $cart->quote(lock: true);

            if ($quote['missing'] || ! $quote['lines'] || ! hash_equals($reviewedHash, $quote['hash'])) {
                throw ValidationException::withMessages(['cart' => 'Your cart changed. Review the latest availability and prices before placing the order.']);
            }

            $order = Order::create($fields + [
                'reference' => (string) Str::ulid(),
                'status' => OrderStatus::Pending,
                'total_amount' => $quote['total'],
            ]);
            $order->items()->createMany(array_map(fn (array $line) => [
                'product_id' => $line['product']->id,
                'product_name' => $line['product']->name,
                'quantity' => $line['quantity'],
                'unit_price' => $line['product']->new_price,
            ], $quote['lines']));

            return $order;
        }, 3);
    }

    public function transition(Order $order, OrderStatus $status): void
    {
        DB::transaction(function () use ($order, $status): void {
            $current = Order::lockForUpdate()->findOrFail($order->id);

            if ($current->status === $status) {
                return;
            }

            if (! in_array($status, $current->status->next(), true)) {
                throw ValidationException::withMessages(['status' => 'That status change is no longer allowed. Refresh and try again.']);
            }

            $current->update(['status' => $status]);
        }, 3);
    }
}
