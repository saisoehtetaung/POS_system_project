<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
     //direct User list page
    public function userList(){
        $users = User::where('role','user')->paginate(4);

        return view('admin.user.list',compact('users'));
    }

    //user change role
    public function changeUserRole(Request $request){
        User::where('id',$request->userId)->update(['role' => $request->role]);
    }

    //delete user account
    public function deleteUserAccount(Request $request){
        User::where('id',$request->userId)->delete();
    }

    // user home page
    public function home(){
        $pizzas = Product::orderBy('created_at','desc')->get();
        $categories = Category::get();
        $carts = Cart::where('user_id',Auth::user()->id)->get();
        $orders = Order::where('user_id',Auth::user()->id)->get();
        return view('user.main.home',compact('pizzas','categories','carts','orders'));
    }

    //change password page
    public function changePasswordPage(){
        return view('user.password.change');
    }

    //change password
    public function changePassword(Request $request){
         $this->passwordValidationCheck($request);

        $user = User::select('password')->where('id',Auth::user()->id)->first();
        $dbHashPassword = $user->password;

        if(Hash::check($request->oldPassword, $dbHashPassword)){
            User::where('id',Auth::user()->id)->update([
                'password' => Hash::make($request->newPassword),
            ]);

            return back()->with(['changeSuccess'=>'Password Change Success...']);
        }

        return back()->with(['notMatch'=>'The Old Password not Match. Try Again.']);
    }

    //user account change
    public function accountChange($id,Request $request){
         $this->accountValidationCheck($request);
        $data = $this->getUserData($request);

        //for image
        if($request->hasFile('image')){
            //old image name | check => delete | store
            $dbImage = User::where('id',$id)->first()->image;

            if($dbImage != null){
                Storage::delete('public/'.$dbImage);
            }

            $fileName= uniqid().$request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('public',$fileName);

            $data['image']=$fileName;
        }

        User::where('id',$id)->update($data);

        return back()->with(['updateSuccess'=>'Admin Account Update...']);
    }

    //user account change page
    public function accountChangePage(){
        return view('user.profile.account');
    }

    //user filter category
    public function filter($categoryid){
        $pizzas = Product::where('category_id',$categoryid)->orderBy('created_at','desc')->get();
        $categories = Category::get();
        $carts = Cart::where('user_id',Auth::user()->id)->get();
        $orders = Order::where('user_id',Auth::user()->id)->get();
        return view('user.main.home',compact('pizzas','categories','carts','orders'));
    }

    //direct pizza details
    public function pizzaDetails($pizzaId){
        $pizzaInfo = Product::where('id',$pizzaId)->first();
        $pizzas = Product::get();
        return view('user.main.detail',compact('pizzaInfo','pizzas'));
    }

    //cart List
    public function cartList(){
        $cartLists = Cart::select('carts.*','products.name as pizza_name','products.price as pizza_price','products.image as pizza_image')
                        ->leftJoin('products','products.id','carts.product_id')
                        ->where('carts.user_id',Auth::user()->id)
                        ->get();

        $totalPrice = 0;
        foreach($cartLists as $cartList){
            $totalPrice += $cartList->pizza_price * $cartList->qty;
        }

        return view('user.cart.cart',compact('cartLists','totalPrice'));
    }

    //order
    public function order(Request $request){
        logger($request);
    }

    //direct history page
    public function history(){
        $orders = Order::where('user_id',Auth::user()->id)->orderBy('created_at','desc')->paginate('7');
        return view('user.main.history',compact('orders'));
    }

     //get request data
    private function getUserData($request){
        return[
            'name'=>$request->name,
            'email'=>$request->email,
            'gender'=>$request->gender,
            'phone'=>$request->phone,
            'address'=>$request->address,
            'updated_at'=>Carbon::now()
        ];
    }

    //account validation check
    private function accountValidationCheck($request){
        Validator::make($request->all(),[
            'name'=>'required',
            'email'=>'required',
            'gender'=>'required',
            'phone'=>'required',
            'image'=>'mimes:png,jpg,jpeg,webp|file',
            'address'=>'required',
        ])->validate();
    }

     //password vlidation check
    private function passwordValidationCheck($request){
        Validator::make($request->all(),[
            'oldPassword'=> 'required|min:6|max:10',
            'newPassword'=> 'required|min:6|max:10',
            'confirmPassword'=> 'required|min:6|max:10|same:newPassword',
        ])->validate();
    }

}
