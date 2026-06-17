<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string|max:1000',
            'phone' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            
            // Validate all variant stocks first
            foreach ($validated['items'] as $itemData) {
                $variant = ProductVariant::lockForUpdate()->findOrFail($itemData['variant_id']);
                
                if ($variant->stock_quantity < $itemData['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for size: {$variant->size}, color: {$variant->color}. Only {$variant->stock_quantity} remaining."
                    ]);
                }
                
                $price = (float) $itemData['price'];
                $totalAmount += $price * $itemData['quantity'];
            }

            // Create Order record
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $validated['shipping_address'],
                'phone' => $validated['phone'],
            ]);

            // Deduct stock levels and save order item lines
            foreach ($validated['items'] as $itemData) {
                $variant = ProductVariant::findOrFail($itemData['variant_id']);
                $variant->decrement('stock_quantity', $itemData['quantity']);

                $order->items()->create([
                    'product_id' => $itemData['product_id'],
                    'variant_id' => $itemData['variant_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Order processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
