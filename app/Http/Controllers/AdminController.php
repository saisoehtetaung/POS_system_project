<?php

namespace App\Http\Controllers;

use Storage;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
        //change password page
    public function changePasswordPage(){
        return view('admin.account.change_password');
    }

    //change password
    public function changePassword(Request $request){
        /*
            1. all field must be fill
            2. new password & confirm password length must be greater than 6 and not greater than 10
            3. new password & confirm password must same
            4. client old password must be same with db password
            5. password change
        */

        $this->passwordValidationCheck($request);

        $user = User::select('password')->where('id',Auth::user()->id)->first();
        $dbHashPassword = $user->password;

        if(Hash::check($request->oldPassword, $dbHashPassword)){
            User::where('id',Auth::user()->id)->update([
                'password' => Hash::make($request->newPassword),
            ]);
            // Auth::logout();
            // return redirect()->route('auth#loginPage');

            return back()->with(['changeSuccess'=>'Password Change Success...']);
        }

        return back()->with(['notMatch'=>'The Old Password not Match. Try Again.']);

        // $clientPassword = Hash::make('sithu');

        // if(Hash::check('sithu', $clientPassword)){
        //     dd('password same');
        // }else{
        //     dd('incorrect');
        // }
        // dd($dbPassword);
    }

    //direct admin details page
    public function details(){
        return view('admin.account.details');
    }

    //direct admin profile page
    public function edit(){
        return view('admin.account.edit');
    }

    //update account
    public function update($id,Request $request){

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

        return redirect()->route('admin#details')->with(['updateSuccess'=>'Admin Account Update...']);
    }

    //admin list
    public function list(){
        $admins = User::when(request('searchkey'),function($query){
            $query->orWhere('name','like',"%".request('searchkey')."%")
                    ->orWhere('email','like',"%".request('searchkey')."%")
                    ->orWhere('gender','like',"%".request('searchkey')."%")
                    ->orWhere('address','like',"%".request('searchkey')."%")
                    ->orWhere('phone','like',"%".request('searchkey')."%");
        })->where('role','admin')
            ->paginate(3);
        $admins->appends(request()->all());
        return view('admin.account.list',compact('admins'));
    }

    //delete admin account
    public function delete($id){
        User::where('id',$id)->delete();
        return back()->with(['deleted'=>"Admin Account Deleted..."]);
    }

    //changeRole admin
    public function changeRole($id){
        $account = User::where('id',$id)->first();
        return view('admin.account.changeRole',compact('account'));
    }

    //change role
    public function change($id,Request $request){
        $data = $this->requestUserData($request);
        User::where('id',$id)->update($data);
        return redirect()->route('admin#list');
    }

    //request user data
    private function requestUserData($request){
        return [
            'role' => $request->role,
        ];
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
