<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create a customer order and adjust stock levels.
     *
     * @param array $data Validated checkout data
     * @param int|null $userId Authed user ID
     * @return Order
     * @throws \Exception
     */
    public function createOrder(array $data, ?int $userId): Order
    {
        return DB::transaction(function () use ($data, $userId) {
            $totalAmount = 0;
            
            // 1. Lock rows and validate variant stock levels first
            foreach ($data['items'] as $itemData) {
                $variant = ProductVariant::lockForUpdate()->findOrFail($itemData['variant_id']);
                
                if ($variant->stock_quantity < $itemData['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for size: {$variant->size}, color: {$variant->color}. Only {$variant->stock_quantity} remaining."
                    ]);
                }
                
                $price = (float) $itemData['price'];
                $totalAmount += $price * $itemData['quantity'];
            }

            // 2. Create the Order record
            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $data['shipping_address'],
                'phone' => $data['phone'],
                'payment_method' => $data['payment_method'] ?? 'cod',
            ]);

            // 3. Deduct stock levels and save order item lines
            foreach ($data['items'] as $itemData) {
                $variant = ProductVariant::findOrFail($itemData['variant_id']);
                $variant->decrement('stock_quantity', $itemData['quantity']);

                $order->items()->create([
                    'product_id' => $itemData['product_id'],
                    'variant_id' => $itemData['variant_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                ]);
            }

            return $order;
        });
    }
}
