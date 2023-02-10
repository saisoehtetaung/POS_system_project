<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    //contact send
    public function contactSend(Request $request){
        Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message
        ]);
        return redirect()->route('user#home')->with(['messageSent'=> "Your message is sent."]);
    }

    //show contact message on admin page
    public function showContact(){
        $contacts = Contact::paginate(5);
        return view('admin.user.contactList', compact('contacts'));
    }

    //show user message
    public function contactInfo($id){
        $contact = Contact::where('id',$id)->first();
        return view('admin.user.contactInfo',compact('contact'));
    }

    //delete user message
    public function deleteContact($id){
        Contact::where('id',$id)->delete();
        return redirect()->route('admin#userShowContact');
    }
}
