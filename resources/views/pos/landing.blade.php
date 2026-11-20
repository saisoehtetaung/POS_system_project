<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cool Stationary POS</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Georgia, "Times New Roman", serif;
            background: linear-gradient(160deg, #f7f1e8 0%, #e6eef5 55%, #d9e7df 100%);
            color: #1f2a24;
        }
        main {
            width: min(520px, 92vw);
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(31, 42, 36, 0.12);
        }
        h1 {
            margin: 0 0 0.75rem;
            font-size: 2rem;
            letter-spacing: 0.02em;
        }
        p {
            margin: 0 0 1.25rem;
            line-height: 1.5;
            color: #405048;
        }
        a {
            color: #0f5c4c;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main>
        <h1>Cool Stationary</h1>
        <p>
            The pizza storefront tables were removed for the offline-first POS schema.
            Use the desktop terminal for billing, or continue to the admin catalog if your role allows it.
        </p>
        @auth
            <p>Signed in as {{ auth()->user()->name }} ({{ auth()->user()->roleName() }}).</p>
            @if(auth()->user()->hasRole('owner', 'manager'))
                <p><a href="{{ route('category#list') }}">Open admin catalog</a></p>
            @endif
            <p><a href="{{ route('logout') }}"
                  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Sign out
            </a></p>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        @endauth
    </main>
</body>
</html>
