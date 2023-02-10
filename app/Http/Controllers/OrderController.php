<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderList;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //direct order list page
    public function orderList(){
        $orders = Order::select('orders.*','users.name as user_name')
                        ->leftJoin('users','users.id','orders.user_id')
                        ->when(request('searchkey'),function($query){
                            $query->where('orders.order_code','like','%'.request('searchkey').'%')
                                ->orWhere('users.name','like','%'.request('searchkey').'%');
                        })
                        ->orderBy('orders.created_at','desc')
                        ->paginate(5);
        return view('admin.order.list',compact('orders'));
    }

    //sort with ajax status
    public function ajaxStatus(Request $request){

        // ->orWhere('orders.status',$request->status)
        // ->paginate(5);
        $orders = Order::select('orders.*','users.name as user_name')
                        ->leftJoin('users','users.id','orders.user_id')
                        ->when(request('searchkey'),function($query){
                            $query->where('orders.order_code','like','%'.request('searchkey').'%')
                                ->orWhere('users.name','like','%'.request('searchkey').'%');
                        })
                        ->orderBy('orders.created_at','desc');

        if($request->status == null){
            $orders = $orders->paginate(5);
        }else{
            $orders = $orders->Where('orders.status',$request->status)->paginate(5);
        }

        return response()->json($orders,200);
    }


    //ajax change status
    public function ajaxChangeStatus(Request $request){
        Order::where('id',$request->orderId)->update([
            'status'=>$request->status
        ]);

    }

    //order list
    public function listInfo($orderCode){
        $order = Order::where('order_code',$orderCode)->first();
        $orderLists = OrderList::select('order_lists.*','users.name as user_name','products.image as product_image','products.name as product_name')
                                ->leftJoin('users','users.id','order_lists.user_id')
                                ->leftJoin('products','products.id','order_lists.product_id')
                                ->where('order_code',$orderCode)
                                ->paginate();

        return view('admin.order.orderList',compact('orderLists','order'));
    }
}
