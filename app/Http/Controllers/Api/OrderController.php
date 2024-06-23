<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderController extends Controller
{
    //User: Create new order
    public function createOrder(Request $request)
    {
        $request->validate([
            'order_item' => 'required|array',
            'order_item.*.product_id' => 'required|integer|exists:products,id',
            'order_item.*.quantity' => 'required|integer|min:1',
            'restaurant_id' => 'required|integer|exists:users,id',
            'shipping_cost' => 'required|integer',
        ]);

        $total_price = 0;

        foreach ($request->order_item as $item) {
            $product = Product::find($item['product_id']);
            $total_price += $product->price * $item['quantity'];
        }

        $total_bill = $total_price + $request->shipping_cost;

        $user = $request->user();
        $data = $request->all();
        $data['user_id'] = $user->id;

        $shipping_address = $user->address;
        $data['shipping_address'] = $shipping_address;

        $shipping_latlong = $user->latlong;
        $data['shipping_latlong'] = $shipping_latlong;

        $data['status'] = 'pending';
        $data['total_price'] = $total_price;
        $data['total_bill'] = $total_bill;

        $order = Order::create($data);

        foreach ($request->order_item as $item) {
            $product = Product::find($item['product_id']);
            $orderItem = new OrderItem([
                'product_id' => $item['product_id'],
                'order_id' => $order->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
            $order->orderItems()->save($orderItem);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => $order
        ]);
    }

    //update purchase status
    public function updatePurchaseStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        $order = Order::find($id);
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }

    //order history
    public function orderHistory(Request $request)
    {
        $user = $request->user();
        $orders = Order::where('user_id', $user->id)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Order history fetched successfully',
            'data' => $orders
        ]);
    }

    //cancel order
    public function cancelOrder(Request $request, $id)
    {
        $order = Order::find($id);
        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order cancelled successfully',
            'data' => $order
        ]);
    }

    //get orders by status for restaurant
    public function getOrdersByStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        $user = $request->user();

        $orders = Order::where('restaurant_id', $user->id)->where('status', $request->status)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Get all orders by status successfully',
            'data' => $orders
        ]);
    }

    //update order status for restaurant
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled,ready_for_delivery,prepared',
        ]);

        $order = Order::find($id);
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }

    //get orders by status for driver
    public function getOrdersByStatusForDriver(Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled,ready_for_delivery,prepared',
        ]);

        $user = $request->user();

        $orders = Order::where('driver_id', $user->id)->where('status', $request->status)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Get all orders by status successfully',
            'data' => $orders
        ]);
    }

    //get order status ready for delivery
    public function getOrderStatusReadyForDelivery(Request $request)
    {
        $orders = Order::with('restaurant')->where('status', 'ready_for_delivery')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Get all orders by status ready for delivery successfully',
            'data' => $orders
        ]);
    }

    //update order status for driver
    public function updateOrderStatusForDriver(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled,on_the_way,delivered',
        ]);

        $order = Order::find($id);
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }
}
