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

                $discountAmount = 0;
                $couponId = null;

                if (!empty($data['coupon_code'])) {
                    $coupon = \App\Models\Coupon::lockForUpdate()->where('code', $data['coupon_code'])->first();
                    if ($coupon && $coupon->isValid()) {
                        $couponId = $coupon->id;
                        if ($coupon->type === 'percent') {
                            $discountAmount = ($totalAmount * $coupon->value) / 100;
                        } else {
                            $discountAmount = min($totalAmount, $coupon->value); // Discount shouldn't exceed total
                        }
                        $totalAmount -= $discountAmount;
                        $coupon->increment('times_used');
                    } else {
                        throw ValidationException::withMessages([
                            'coupon_code' => 'Invalid or expired coupon.'
                        ]);
                    }
                }

                // 2. Create the Order record
                $order = Order::create([
                    'user_id' => $userId,
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'coupon_id' => $couponId,
                    'status' => 'pending',
                    'shipping_address' => $data['shipping_address'],
                    'township' => $data['township'] ?? null,

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
