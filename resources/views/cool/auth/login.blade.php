@extends('cool.layouts.guest')

@section('title', 'Sign in')

@section('content')
    <div class="cs-panel p-6 sm:p-8">
        <h1 class="brand-font text-3xl font-bold">Sign in</h1>
        <p class="mt-2 text-sm text-[var(--cs-muted)]">Use your Login ID and password to access Cool Stationary.</p>

        @if ($errors->any())
            <div class="mt-4 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-semibold">Login ID</label>
                <input class="cs-input mt-1" type="text" name="login_id" value="{{ old('login_id') }}" required autofocus autocomplete="username">
            </div>
            <div>
                <label class="text-sm font-semibold">Password</label>
                <input class="cs-input mt-1" type="password" name="password" required autocomplete="current-password">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-[var(--cs-muted)]">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button class="cs-btn-primary w-full" type="submit">Sign in to Cool Stationary</button>
        </form>
    </div>
@endsection
