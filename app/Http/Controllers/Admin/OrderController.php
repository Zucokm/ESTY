<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display a listing of all customer orders.
     */
    public function index()
    {
        $orders = Order::with(['user', 'items.product.images', 'items.variant'])->latest()->get();
        
        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders
        ]);
    }

    /**
     * Update the status of a specific order.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with('items.variant')->findOrFail($id);
        $oldStatus = $order->status;
        
        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,packing,shipping,delivered,completed,cancelled,returned,refunded'
        ]);

        $newStatus = $validated['status'];
        
        // Check if we need to restore stock (transitioning to cancelled/returned/refunded)
        $restoreStatuses = ['cancelled', 'returned', 'refunded'];
        if (!in_array($oldStatus, $restoreStatuses) && in_array($newStatus, $restoreStatuses)) {
            foreach ($order->items as $item) {
                if ($item->variant) {
                    $item->variant->increment('stock_quantity', $item->quantity);
                }
            }
        } 
        // Check if we need to deduct stock (transitioning back from cancelled/returned to active)
        elseif (in_array($oldStatus, $restoreStatuses) && !in_array($newStatus, $restoreStatuses)) {
            foreach ($order->items as $item) {
                if ($item->variant) {
                    $item->variant->decrement('stock_quantity', $item->quantity);
                }
            }
        }

        $order->update([
            'status' => $newStatus
        ]);
        
        if ($newStatus === 'shipping' && $order->user) {
            try {
                \Illuminate\Support\Facades\Mail::to($order->user)->send(new \App\Mail\OrderShipped($order));
            } catch (\Exception $e) {}
        }

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
}
