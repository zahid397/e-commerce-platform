<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_cart_quantities_validate_and_favorites_can_be_removed(): void
    {
        $product = Product::factory()->create(['new_price' => '10.10']);
        $this->post(route('cart.store', $product), ['quantity' => 0])->assertSessionHasErrors('quantity');
        $this->post(route('cart.store', $product), ['quantity' => 2])->assertRedirect(route('cart.index'));
        $this->patch(route('cart.update', $product), ['quantity' => 3])->assertRedirect();
        $this->get(route('cart.index'))->assertOk()->assertSee('30.30');

        $this->post(route('favorites.toggle', $product))->assertRedirect();
        $this->get(route('favorites.index'))->assertOk()->assertSee($product->name);
        $this->post(route('favorites.toggle', $product))->assertRedirect();
        $this->get(route('favorites.index'))->assertOk()->assertDontSee($product->name);
        $this->delete(route('cart.destroy', $product->id))->assertRedirect();
        $this->get(route('cart.index'))->assertOk()->assertSee('Your cart is empty');
    }

    public function test_checkout_saves_server_priced_order_once_and_receipts_are_private(): void
    {
        $product = Product::factory()->create(['new_price' => '10.10']);
        $this->post(route('cart.store', $product), ['quantity' => 3])->assertRedirect();
        $this->get(route('checkout.show'))->assertOk();
        $fields = $this->deliveryFields() + ['checkout_token' => session('checkout.token'), 'total_amount' => '0.01'];

        $this->post(route('checkout.store'), $fields)->assertRedirect();
        $order = Order::firstOrFail();
        $receipt = route('orders.receipt', $order->reference);

        $this->assertSame('30.30', $order->total_amount);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 3,
            'unit_price' => '10.10',
        ]);
        $this->get($receipt)->assertOk()->assertSee($order->reference);
        $this->post(route('checkout.store'), $fields)->assertRedirect($receipt);
        $this->assertDatabaseCount('orders', 1);
        $this->get(route('cart.index'))->assertSee('Your cart is empty');

        session()->flush();
        $this->get($receipt)->assertNotFound();
    }

    public function test_checkout_requires_valid_contact_details_and_rejects_a_changed_quote(): void
    {
        $product = Product::factory()->create(['new_price' => '20.00']);
        $this->post(route('cart.store', $product), ['quantity' => 1]);
        $this->get(route('checkout.show'))->assertOk();

        $token = session('checkout.token');
        $this->post(route('checkout.store'), ['checkout_token' => $token])->assertSessionHasErrors(['name', 'email', 'phone', 'address']);
        $product->update(['new_price' => '25.00']);
        $this->post(route('checkout.store'), $this->deliveryFields() + ['checkout_token' => $token])
            ->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->get(route('checkout.show'))->assertOk()->assertSee('25.00');
    }

    public function test_checkout_rejects_a_product_deactivated_after_review(): void
    {
        $product = Product::factory()->create();
        $this->post(route('cart.store', $product), ['quantity' => 1]);
        $this->get(route('checkout.show'))->assertOk();

        $product->update(['is_active' => false]);
        $this->post(route('checkout.store'), $this->deliveryFields() + ['checkout_token' => session('checkout.token')])
            ->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->post(route('cart.store', $product), ['quantity' => 1])->assertNotFound();
    }

    public function test_admin_can_process_order_and_history_survives_product_deletion(): void
    {
        $product = Product::factory()->create(['new_price' => '32.50']);
        $this->post(route('cart.store', $product), ['quantity' => 1]);
        $this->get(route('checkout.show'))->assertOk();
        $this->post(route('checkout.store'), $this->deliveryFields() + ['checkout_token' => session('checkout.token')]);
        $order = Order::firstOrFail();

        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.orders.index'))->assertOk()->assertSee($order->reference);
        $this->patch(route('admin.orders.update', $order), ['status' => 'completed'])->assertSessionHasErrors('status');
        $this->patch(route('admin.orders.update', $order), ['status' => 'processing'])->assertRedirect();
        $this->patch(route('admin.orders.update', $order), ['status' => 'completed'])->assertRedirect();
        $this->patch(route('admin.orders.update', $order), ['status' => 'cancelled'])->assertSessionHasErrors('status');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->delete(route('admin.products.destroy', $product))->assertRedirect();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => $product->name,
            'unit_price' => '32.50',
        ]);
        $this->get(route('admin.orders.show', $order))->assertOk()->assertSee($product->name);
    }

    private function deliveryFields(): array
    {
        return [
            'name' => 'Test Shopper',
            'email' => 'shopper@example.test',
            'phone' => '+8801700000000',
            'address' => 'Example street, Dhaka',
        ];
    }
}
