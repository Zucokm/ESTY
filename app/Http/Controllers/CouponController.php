<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        
        $coupon = Coupon::where('code', $request->code)->first();
        
        if (!$coupon || !$coupon->isValid()) {
            return response()->json(['message' => 'Invalid or expired coupon code.'], 400);
        }
        
        return response()->json([
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value
        ]);
    }
}
