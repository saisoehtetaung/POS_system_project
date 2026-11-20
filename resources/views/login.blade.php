@extends('layouts.master')

@section('title', 'Login')

@section('content')
    <div class="login-form">
        @error('terms')
            <small class="text-danger">{{ $message }}</small>
        @enderror
        <form action="{{ route('login') }}" method="post">
            @csrf
            <div class="form-group">
                <label>Login ID</label>
                <input class="au-input au-input--full" type="text" name="login_id" placeholder="A001 / M001 / S001" value="{{ old('login_id') }}">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input class="au-input au-input--full" type="password" name="password" placeholder="Password">
            </div>

            <button class="au-btn au-btn--block au-btn--green m-b-20" type="submit">sign
                in</button>

        </form>
        <div class="register-link">
            <p>
                Don't you have account?
                <a href="{{ route('auth#registerPage') }}">Sign Up Here</a>
            </p>
        </div>
    </div>
@endsection
