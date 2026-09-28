<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        // Get all users who are not admins
        $customers = User::where('role', '!=', 'admin')
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->latest()
            ->get();

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers
        ]);
    }

    public function toggleBan(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot ban an admin user.');
        }

        $user->is_banned = !$user->is_banned;
        $user->save();

        $status = $user->is_banned ? 'banned' : 'unbanned';
        return back()->with('success', "Customer successfully {$status}.");
    }
}
