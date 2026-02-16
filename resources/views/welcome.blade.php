<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Codenames Generator</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
            /* Grayscale radial gradient for a "studio" look */
            background: radial-gradient(circle, #2c2c2c 0%, #000000 100%);
        }

        .contrast-text {
            color: #ffffff;
            /* Subtle white glow shadow for high contrast */
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.3), 0 2px 4px rgba(0,0,0,0.5);
        }

        .glass-panel {
            background: rgba(22, 22, 21, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        code {
            text-shadow: none;
        }
    </style>
</head>
<body class="antialiased text-zinc-300 flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">

<header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6">
    @if (Route::has('login'))
        <nav class="flex items-center justify-end gap-4">
            @auth
                <a href="{{ url('/dashboard') }}" class="px-5 py-1.5 text-white border border-zinc-700 hover:border-white rounded-md transition-all">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="px-5 py-1.5 text-zinc-400 hover:text-white transition-all">
                    Log in
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="px-5 py-1.5 text-white border border-zinc-700 hover:border-white rounded-md transition-all">
                        Register
                    </a>
                @endif
            @endauth
        </nav>
    @endif
</header>

<div class="flex items-center justify-center w-full transition-opacity duration-750 lg:grow">
    <main class="w-full max-w-4xl glass-panel rounded-2xl p-8 lg:p-12 shadow-2xl">

        <h1 class="text-4xl font-semibold contrast-text tracking-tight">
            🎲 Codenames Card Generator
        </h1>

        <p class="mt-6 text-lg text-zinc-400 leading-relaxed max-w-3xl">
            A high-performance Laravel engine for generating printable card boards.
            Optimized for <span class="text-white">A4 duplex printing</span> with zero-margin precision.
        </p>

        <div class="mt-12 grid gap-8 md:grid-cols-3">
            <div class="group">
                <h3 class="font-medium text-white text-lg group-hover:text-blue-400 transition-colors">🖨 Printable PDF</h3>
                <p class="mt-2 text-sm text-zinc-500 leading-normal">
                    Fixed 3×4 grid alignment. Perfectly mirrored for double-sided production.
                </p>
            </div>

            <div class="group">
                <h3 class="font-medium text-white text-lg group-hover:text-blue-400 transition-colors">🧩 Image Assets</h3>
                <p class="mt-2 text-sm text-zinc-500 leading-normal">
                    Pre-rendered square card geometry. No runtime latency or API dependencies.
                </p>
            </div>

            <div class="group">
                <h3 class="font-medium text-white text-lg group-hover:text-blue-400 transition-colors">⚙️ CLI Core</h3>
                <p class="mt-2 text-sm text-zinc-500 leading-normal">
                    Artisan-powered batch processing for automated deck generation.
                </p>
            </div>
        </div>

        <div class="mt-12 p-1 bg-gradient-to-r from-zinc-800 to-transparent rounded-lg">
            <div class="bg-black rounded-lg p-6">
                <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <span class="size-2 bg-green-500 rounded-full animate-pulse"></span>
                    Terminal Command
                </h2>
                <code class="text-green-400 font-mono text-sm block">
                    php artisan app:generate-code-names-pdf
                </code>
            </div>
        </div>

        <p class="mt-6 text-xs text-zinc-600 uppercase tracking-widest">
            Export Path:
            <span class="text-zinc-400 px-2 py-1 bg-zinc-900 rounded ms-2">
                        storage/code_names_pdf/code_names.pdf
                    </span>
        </p>

    </main>
</div>

<footer class="mt-8 text-zinc-600 text-xs">
    &copy; {{ date('Y') }} — Codenames Engine v2.0
</footer>
</body>
</html>
