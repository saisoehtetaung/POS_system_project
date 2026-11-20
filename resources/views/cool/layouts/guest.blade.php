<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in') · Cool Stationary</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen grid lg:grid-cols-2">
        <section class="relative hidden lg:flex items-end p-12 overflow-hidden"
                 style="background:
                    linear-gradient(160deg, rgba(28,36,52,0.88), rgba(15,118,110,0.72)),
                    url('https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=1600&q=80') center/cover;">
            <div class="relative z-10 text-white max-w-md">
                <div class="brand-font text-5xl font-bold mb-4">Cool Stationary</div>
                <p class="text-lg text-white/85 leading-relaxed">
                    Offline-first POS and inventory for pens, paper, and school sets — built for fast checkout and stock control.
                </p>
            </div>
        </section>

        <section class="flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md">
                <div class="lg:hidden mb-8">
                    <div class="brand-font text-3xl font-bold">Cool Stationary</div>
                    <p class="text-sm text-[var(--cs-muted)] mt-1">POS & Inventory Management</p>
                </div>
                @yield('content')
            </div>
        </section>
    </div>
</body>
</html>
