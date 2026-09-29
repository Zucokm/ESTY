<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\ReturnMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ReturnRequestController extends Controller
{
    /**
     * Submit a new return request for an order.
     */
    public function store(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)->where('user_id', Auth::id())->firstOrFail();

        // Check if a request already exists
        if ($order->returnRequest) {
            return redirect()->back()->withErrors(['message' => 'A return request already exists for this order.']);
        }

        // Allow returns only for delivered orders, within 7 days
        // (Skipping 7-day exact check for simplicity in demo, but ensuring it's delivered)
        if ($order->status !== 'delivered') {
            return redirect()->back()->withErrors(['message' => 'Returns are only available for delivered orders.']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'description' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120' // 5MB per image max
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('returns', 'public');
                $imagePaths[] = '/storage/' . $path;
            }
        }

        $returnRequest = ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'status' => 'pending',
            'images' => empty($imagePaths) ? null : $imagePaths,
        ]);

        // Change order status to indicate a return is requested
        $order->update(['status' => 'return_requested']);

        return redirect()->back()->with('success', 'Return request submitted successfully. Our team will review it shortly.');
    }

    /**
     * Customer sends a message in a return request ticket.
     */
    public function show($id)
    {
        $returnRequest = ReturnRequest::with(['order', 'messages'])->where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return Inertia::render('ReturnRequests/Show', [
            'returnRequest' => $returnRequest
        ]);
    }

    public function message(Request $request, $returnRequestId)
    {
        $returnRequest = ReturnRequest::where('id', $returnRequestId)->where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        ReturnMessage::create([
            'return_request_id' => $returnRequest->id,
            'user_id' => Auth::id(),
            'is_admin' => false,
            'message' => $validated['message']
        ]);

        return redirect()->back()->with('success', 'Message sent.');
    }
}
