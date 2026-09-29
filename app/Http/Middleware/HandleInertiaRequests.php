<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'customer_unread_returns' => fn () => $request->user() ? \App\Models\ReturnMessage::whereHas('returnRequest', function($q) use ($request) { $q->where('user_id', $request->user()->id); })->where('is_admin', true)->where('is_read', false)->count() : 0,

            'admin_unread_returns' => fn () => $request->user() && $request->user()->role === 'admin' ? \App\Models\ReturnRequest::where('status', 'pending')->orWhereHas('messages', function($q) {
                $q->where('is_admin', false)->where('is_read', false);
            })->count() : 0,

            'active_coupons' => \App\Models\Coupon::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('valid_until')
                          ->orWhere('valid_until', '>', now());
                })
                ->where(function ($query) {
                    $query->whereNull('usage_limit')
                          ->orWhereColumn('times_used', '<', 'usage_limit');
                })
                ->get(),
        ];
    }
}
