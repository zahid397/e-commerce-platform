<?php

namespace App\Services;

use App\Models\Product;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final readonly class Cart
{
    public const MAX_QUANTITY = 99;

    public const MAX_PRODUCTS = 25;

    public function __construct(private Request $request) {}

    /** @return array<int, int> */
    public function quantities(): array
    {
        return $this->request->session()->get('cart', []);
    }

    public function set(int $id, int $quantity, bool $add = false): void
    {
        $items = $this->quantities();
        $quantity += $add ? ($items[$id] ?? 0) : 0;
        if ($quantity < 1 || $quantity > self::MAX_QUANTITY) {
            throw ValidationException::withMessages(['quantity' => 'Choose between 1 and 99 units per product.']);
        }

        if (! isset($items[$id]) && count($items) >= self::MAX_PRODUCTS) {
            throw ValidationException::withMessages(['cart' => 'Your cart can hold up to 25 different products.']);
        }

        $items[$id] = $quantity;
        $this->save($items);
    }

    public function remove(int $id): void
    {
        $items = $this->quantities();
        unset($items[$id]);
        $this->save($items);
    }

    public function clear(): void
    {
        $this->save([]);
    }

    public function quote(bool $lock = false): array
    {
        $items = $this->quantities();
        ksort($items);

        $products = Product::active()->whereKey(array_keys($items))->orderBy('id')
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->get()->keyBy('id');
        $missing = array_values(array_diff(array_keys($items), $products->keys()->all()));
        $lines = [];
        $fingerprint = [];
        $total = 0;

        foreach ($products as $id => $product) {
            $quantity = $items[$id];
            $price = Money::minor($product->new_price);
            $lineTotal = $price * $quantity;
            $total += $lineTotal;
            $fingerprint[] = [$id, $quantity, $price, $product->name];
            $lines[] = ['product' => $product, 'quantity' => $quantity, 'subtotal' => Money::decimal($lineTotal)];
        }

        return [
            'lines' => $lines,
            'missing' => $missing,
            'total' => Money::decimal($total),
            'hash' => hash('sha256', json_encode($fingerprint, JSON_THROW_ON_ERROR)),
        ];
    }

    private function save(array $items): void
    {
        $session = $this->request->session();
        $session->put('cart', $items);
        $session->forget('checkout');
    }
}
