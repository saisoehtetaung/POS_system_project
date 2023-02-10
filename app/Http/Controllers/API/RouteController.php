<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RouteController extends Controller
{
    //get all product list
    public function productList(){
        $products = Product::get();
        return response()->json($products,200);
    }

    //get category list
    public function categoryList(){
        $categories = Category::orderBy('id','desc')->get();
        return response()->json($categories,200);
    }

    //get contact list
    public function contactList(){
        $contacts = Contact::orderBy('created_at','desc')->get();
        return response()->json($contacts,200);
    }

    //create category
    public function categoryCreate(Request $request){
        // dd($request->all());
        // dd($request->header('description'));

        $data = [
            'name' => $request->name,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

        $response = Category::create($data);
        return response()->json($response,200);
    }

    //create Contact
    public function createContact(Request $request){
        $data = $this->getContactData($request);
        Contact::create($data);

        $contacts = Contact::get();
        return response()->json($contacts,200);
    }

    //delete category
    public function deleteCategory($id){
        $data = Category::where('id',$id)->first();

        if(isset($data)){
            Category::where('id',$id)->delete();
            return response()->json(['status'=>true,'message'=>'delete success','deleteData'=>$data],200);
        }

        return response()->json(['status'=>false,'message'=>'There is no category..'],200);
    }

    //category detail
    public function categoryDetails(Request $request){
         $data = Category::where('id',$request->category_id)->first();

        if(isset($data)){
            return response()->json(['status'=>true,'category'=>$data],200);
        }

        return response()->json(['status'=>false,'message'=>'There is no category..'],200);
    }

    //category update
    public function categoryUpdate(Request $request){
        $categoryId = $request->category_id;

        $dbSource = Category::where('id',$categoryId)->first();

        if(isset($dbSource)){
            $data = $this->getCategoryData($request);
            Category::where('id',$categoryId)->update($data);
            $response = Category::where('id',$categoryId)->get();
            return response()->json(['status'=>true,'message'=>'category update success.','updated category'=>$response],200);
        }



        return response()->json(['status'=>false,'message'=>'there is no category here...'],500);
    }

    //get contact data
    private function getContactData($request){
        return [
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
        ];
    }

    //get category data
    private function getCategoryData($request){
        return[
            'name'=>$request->category_name,
            'updated_at' => Carbon::now(),
        ];
    }
}
