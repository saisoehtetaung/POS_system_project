<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AjaxController extends Controller
{
    //return pizza list
    public function pizzaList(Request $request){
        if($request->status == 'asc'){
            $data = Product::orderBy('created_at','asc')->get();
        }else{
            $data = Product::orderBy('created_at','desc')->get();
        }

        return response()->json($data,200);
    }

    // return add to cart
    public function addToCart(Request $request){
        // logger($request->all());
        $data = $this->getOrderData($request);
        // logger($data);
        Cart::create($data);

        $response = [
            'message' => 'Add To Cart Completed',
            'status' => 'success'
        ];
        return response()->json($response,200);
    }

    //order
    public function order(Request $request){
        $total = 0;

        foreach($request->all() as $item){
            $data = OrderList::create([
                'user_id' => $item['userId'],
                'product_id' => $item['productId'],
                'qty' => $item['qty'],
                'total' => (int)$item['total'],
                'order_code' => $item['orderCode'],
            ]);

            $total += $data->total;
        }

        Cart::where('user_id',Auth::user()->id)->delete();

        Order::create([
            'user_id' => Auth::user()->id,
            'order_code' => $data->order_code,
            'total_price' => $total + 3000,
        ]);

        return response()->json([
            'status' => 'true'
        ],200);
    }

    //clear cart
    public function clearCart(){
        Cart::where('user_id',Auth::user()->id)->delete();
    }

    //clear current product
    public function clearProduct(Request $request){
        Cart::where('user_id',Auth::user()->id)->where('id',$request->cartId)->delete();
    }

    //increase pizza view count
    public function increaseViewCount(Request $request){
        $product = Product::where('id',$request->productId)->first();
        Product::where('id',$request->productId)->update([
            'view_count' => $product->view_count +1,
        ]);
    }


    //get order data
    private function getOrderData($request){
        return[
            'user_id' => $request->userId,
            'product_id' => $request->pizzaId,
            'qty' => $request->count,
            'created_at' => Carbon::now()
        ];
    }
}
