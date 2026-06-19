<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        try {
            return DB::transaction(function () use ($data, $userId) {
                $totalAmount = 0;
                $verifiedPrices = [];
                
                // 1. Lock rows, validate variant stock levels, and securely resolve prices first
                foreach ($data['items'] as $itemData) {
                    $variant = ProductVariant::with('product')->lockForUpdate()->findOrFail($itemData['variant_id']);
                    
                    if ($variant->stock_quantity < $itemData['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => "Insufficient stock for size: {$variant->size}, color: {$variant->color}. Only {$variant->stock_quantity} remaining."
                        ]);
                    }
                    
                    // Securely resolve the product price from the database state
                    $actualPrice = (float) $variant->product->base_price + (float) ($variant->additional_price ?? 0);
                    $verifiedPrices[$variant->id] = $actualPrice;
                    
                    $totalAmount += $actualPrice * $itemData['quantity'];
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
                        'price' => $verifiedPrices[$variant->id],
                    ]);
                }

                return $order;
            });
        } catch (\Exception $e) {
            Log::error('Order creation transaction failed: ' . $e->getMessage(), [
                'user_id' => $userId,
                'items_count' => count($data['items'] ?? []),
                'phone' => $data['phone'] ?? null,
                'exception' => $e
            ]);
            throw $e;
        }
    }
}
