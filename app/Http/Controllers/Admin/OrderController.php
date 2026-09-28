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
        $order = Order::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,packing,shipping,delivered,completed,cancelled'
        ]);

        $order->update([
            'status' => $validated['status']
        ]);
        
        if ($validated['status'] === 'shipping' && $order->user) {
            try {
                \Illuminate\Support\Facades\Mail::to($order->user)->send(new \App\Mail\OrderShipped($order));
            } catch (\Exception $e) {}
        }

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
}
