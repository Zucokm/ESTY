<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = Auth::user()->wishlists()->with(['product.images'])->get()->pluck('product');
        return response()->json($wishlistItems);
    }

    public function toggle(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        
        $userId = Auth::id();
        $productId = $request->product_id;
        
        $wishlistItem = Wishlist::where('user_id', $userId)->where('product_id', $productId)->first();
        
        if ($wishlistItem) {
            $wishlistItem->delete();
            return response()->json(['status' => 'removed']);
        } else {
            Wishlist::create(['user_id' => $userId, 'product_id' => $productId]);
            return response()->json(['status' => 'added']);
        }
    }
}
