<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('cool.auth.login');
    }

    public function registerPage()
    {
        return redirect()->route('auth#loginPage');
    }

    public function dashboard()
    {
        if (! Auth::check()) {
            return redirect()->route('auth#loginPage');
        }

        if (Auth::user()->hasRole('cashier', 'manager')) {
            return redirect()->route('cool.pos.index');
        }

        return redirect()->route('cool.dashboard');
    }
}
