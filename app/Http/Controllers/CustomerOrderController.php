<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CustomerOrderController extends Controller
{
    /**
     * Display customer order history.
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product.images', 'items.variant'])
            ->latest()
            ->get();

        return Inertia::render('Orders/Index', [
            'orders' => $orders
        ]);
    }

    /**
     * Cancel a pending order and restore inventory stock.
     */
    public function cancel($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        if ($order->status !== 'pending') {
            throw ValidationException::withMessages([
                'order' => 'Only pending orders can be cancelled.'
            ]);
        }

        // Enforce 30-minute time limit for cancellation
        if ($order->created_at->diffInMinutes(now()) > 30) {
            throw ValidationException::withMessages([
                'order' => 'Orders can only be cancelled within 30 minutes of placement.'
            ]);
        }

        try {
            DB::beginTransaction();

            $order->update(['status' => 'cancelled']);

            // Restore stock levels for each item
            foreach ($order->items as $item) {
                $variant = ProductVariant::find($item->variant_id);
                if ($variant) {
                    $variant->increment('stock_quantity', $item->quantity);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Order cancelled and stock restored successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
