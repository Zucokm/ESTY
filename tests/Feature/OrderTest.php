<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_order_with_cod_payment_method(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        
        $category = Category::create([
            'name' => 'Shirts',
            'slug' => 'shirts',
            'description' => 'Fine shirts',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Lounge Shirt',
            'slug' => 'lounge-shirt',
            'base_price' => 50.00,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Sage',
            'stock_quantity' => 10,
            'sku' => 'lounge-shirt-sage-m',
            'additional_price' => 0.00,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('checkout.store'), [
                'shipping_address' => '123 Main St, Yangon',
                'phone' => '091234567',
                'payment_method' => 'cod',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'quantity' => 2,
                        'price' => 50.00,
                    ]
                ]
            ]);

        $response->assertSessionHasNoErrors();
        
        // Assert order created
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'status' => 'pending',
            'shipping_address' => '123 Main St, Yangon',
            'phone' => '091234567',
            'payment_method' => 'cod',
        ]);

        // Assert variant stock decremented
        $variant->refresh();
        $this->assertEquals(8, $variant->stock_quantity);
    }

    public function test_customer_can_place_order_with_bank_transfer(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        
        $category = Category::create([
            'name' => 'Shirts',
            'slug' => 'shirts',
            'description' => 'Fine shirts',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Lounge Shirt',
            'slug' => 'lounge-shirt',
            'base_price' => 50.00,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Sage',
            'stock_quantity' => 10,
            'sku' => 'lounge-shirt-sage-m',
            'additional_price' => 0.00,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('checkout.store'), [
                'shipping_address' => '456 Second St, Yangon',
                'phone' => '099876543',
                'payment_method' => 'bank_transfer',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'quantity' => 1,
                        'price' => 50.00,
                    ]
                ]
            ]);

        $response->assertSessionHasNoErrors();

        // Assert order created with bank_transfer
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => 50.00,
            'payment_method' => 'bank_transfer',
        ]);

        $variant->refresh();
        $this->assertEquals(9, $variant->stock_quantity);
    }

    public function test_cannot_place_order_with_insufficient_stock(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        
        $category = Category::create([
            'name' => 'Shirts',
            'slug' => 'shirts',
            'description' => 'Fine shirts',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Lounge Shirt',
            'slug' => 'lounge-shirt',
            'base_price' => 50.00,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Sage',
            'stock_quantity' => 2,
            'sku' => 'lounge-shirt-sage-m',
            'additional_price' => 0.00,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('checkout.store'), [
                'shipping_address' => '456 Second St, Yangon',
                'phone' => '099876543',
                'payment_method' => 'bank_transfer',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'quantity' => 5, // more than stock
                        'price' => 50.00,
                    ]
                ]
            ]);

        $response->assertSessionHasErrors('items');
        
        // Assert order not created
        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->id,
        ]);

        // Stock unchanged
        $variant->refresh();
        $this->assertEquals(2, $variant->stock_quantity);
    }

    public function test_order_price_spoofing_is_ignored_and_actual_price_used(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        
        $category = Category::create([
            'name' => 'Shirts',
            'slug' => 'shirts',
            'description' => 'Fine shirts',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Lounge Shirt',
            'slug' => 'lounge-shirt',
            'base_price' => 50.00,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Sage',
            'stock_quantity' => 10,
            'sku' => 'lounge-shirt-sage-m',
            'additional_price' => 15.00, // Total price: 50.00 + 15.00 = 65.00
        ]);

        // Attempt price spoofing by claiming the price is 1.00
        $response = $this
            ->actingAs($user)
            ->post(route('checkout.store'), [
                'shipping_address' => '123 Main St, Yangon',
                'phone' => '091234567',
                'payment_method' => 'cod',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'quantity' => 2,
                        'price' => 1.00, // Spoofed price
                    ]
                ]
            ]);

        $response->assertSessionHasNoErrors();
        
        // Assert order created with CORRECT price (65.00 * 2 = 130.00) instead of spoofed price (1.00 * 2 = 2.00)
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => 130.00,
        ]);

        $order = Order::where('user_id', $user->id)->first();
        
        // Assert order item has secure resolved price (65.00)
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'price' => 65.00,
        ]);
    }
}
