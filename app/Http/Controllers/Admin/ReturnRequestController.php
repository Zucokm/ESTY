<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Models\ReturnMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ReturnRequestController extends Controller
{
    public function index()
    {
        $requests = ReturnRequest::with(['order', 'user'])->latest()->get();
        return Inertia::render('Admin/ReturnRequests/Index', [
            'returnRequests' => $requests
        ]);
    }

    public function show($id)
    {
        $returnRequest = ReturnRequest::with(['order.items.product', 'order.items.variant', 'user', 'messages.user'])->findOrFail($id);
        return Inertia::render('Admin/ReturnRequests/Show', [
            'returnRequest' => $returnRequest
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $returnRequest = ReturnRequest::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|string|in:pending,reviewing,approved,rejected,completed'
        ]);

        $newStatus = $validated['status'];
        
        // If rejected, order status goes back to delivered
        if ($newStatus === 'rejected') {
            $returnRequest->order->update(['status' => 'delivered']);
        }
        
        // If completed (refunded), we trigger the Order stock restoration
        if ($newStatus === 'completed' && $returnRequest->status !== 'completed') {
            // Update order status to refunded which we handle in OrderController, 
            // but actually we can just manually restore stock here.
            $returnRequest->order->update(['status' => 'refunded']);
            
            foreach ($returnRequest->order->items as $item) {
                if ($item->variant) {
                    $item->variant->increment('stock_quantity', $item->quantity);
                }
            }
        }

        $returnRequest->update([
            'status' => $newStatus
        ]);

        return redirect()->back()->with('success', 'Return request status updated.');
    }

    public function message(Request $request, $id)
    {
        $returnRequest = ReturnRequest::findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        ReturnMessage::create([
            'return_request_id' => $returnRequest->id,
            'user_id' => Auth::id(),
            'is_admin' => true,
            'message' => $validated['message']
        ]);

        return redirect()->back()->with('success', 'Message sent.');
    }
}
