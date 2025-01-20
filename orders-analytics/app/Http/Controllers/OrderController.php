<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'order_date' => 'required|date',
        ]);

        DB::table('orders')->insert($validated);

        return response()->json(['message' => 'Order added successfully'], 201);
    }

    public function analytics()
    {
        $totalRevenue = DB::table('orders')->sum(DB::raw('quantity * price'));
        $topProducts = DB::table('orders')
            ->select('product_id', DB::raw('SUM(quantity * price) as revenue'))
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        $lastMinuteOrders = DB::table('orders')
            ->where('order_date', '>=', Carbon::now()->subMinute())
            ->count();

        $revenueLastMinute = DB::table('orders')
            ->where('order_date', '>=', Carbon::now()->subMinute())
            ->sum(DB::raw('quantity * price'));

        return response()->json([
            'total_revenue' => $totalRevenue,
            'top_products' => $topProducts,
            'orders_last_minute' => $lastMinuteOrders,
            'revenue_last_minute' => $revenueLastMinute,
        ]);
    }
}

