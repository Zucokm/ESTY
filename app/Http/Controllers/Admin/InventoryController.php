<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        // Get all variants ordered by stock
        $inventory = ProductVariant::with('product')
            ->orderBy('stock_quantity', 'asc')
            ->get();

        return Inertia::render('Admin/Inventory/Index', [
            'inventory' => $inventory
        ]);
    }
}
