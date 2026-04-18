<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['orders'] = DB::table('orders')->paginate(10);
        $data['title'] = 'Orders';
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['orders'],
                'meta' => ['title' => $data['title']],
            ]);
        }
        return redirect()->to(spa_url('orders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $products = DB::table('products')->select('id', 'name')->get();
        $data['title']       = 'Place Order';
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title'], 'products' => $products]]);
        }
        return redirect()->to(spa_url('orders/create'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreOrderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantities.*' => 'required|integer|min:0',
            'product_ids.*' => 'required|exists:products,id',
        ]);
    
        foreach ($request->product_ids as $index => $productId) {
            $quantity = $request->quantities[$index];
            if ($quantity > 0) {
                Order::create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'status' => 'new', // Default status
                ]);
            }
        }
    
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Order placed successfully!']);
        }
        return redirect()->route('order.index')->with('success', 'Order placed successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['data' => $order]);
        }
        return redirect()->to(spa_url('orders'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        if ($request->expectsJson()) {
            return response()->json(['data' => $order, 'meta' => ['title' => 'Edit order']]);
        }
        return redirect()->to(spa_url('orders/'.$id.'/edit'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOrderRequest  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOrderRequest $request, $id): JsonResponse|RedirectResponse
    {
        DB::table('orders')->where('id', $id)->update(['updated_at' => now()]);
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Order updated successfully']);
        }
        return redirect()->route('order.index')->with('success', 'Order updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id): JsonResponse|RedirectResponse
    {
        DB::table('orders')->where('id', $id)->delete();
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Order deleted successfully']);
        }
        return redirect()->route('order.index')->with('success', 'Order deleted successfully');
    }

    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {
        return $this->destroy($request, $id);
    }
}
