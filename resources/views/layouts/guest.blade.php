<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'GoldenDrinks') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400;1,500&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #050505;
            --fg: #f4f4f2;
            --muted: rgba(244, 244, 242, 0.58);
            --faint: rgba(244, 244, 242, 0.12);
            --line: rgba(244, 244, 242, 0.18);
            --gold: #c9a55a;
            --gold-soft: #dfc07a;
            --danger: #e08a8a;
            --ok: #9ecf9a;
            --display: "Cormorant Garamond", Georgia, serif;
            --sans: "Outfit", system-ui, sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100svh;
            font-family: var(--sans);
            color: var(--fg);
            background: var(--bg);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        a { color: inherit; text-decoration: none; }

        .auth {
            position: relative;
            min-height: 100svh;
            display: grid;
            isolation: isolate;
        }

        /* Full-bleed cinematic backdrop */
        .auth__world {
            position: absolute;
            inset: 0;
            z-index: -3;
            overflow: hidden;
        }

        .auth__world img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 35%;
            filter: grayscale(1) contrast(1.12) brightness(0.42);
            transform: scale(1.08);
            animation: drift 28s ease-in-out infinite alternate;
        }

        .auth__veil {
            position: absolute;
            inset: 0;
            z-index: -2;
            background:
                radial-gradient(ellipse 55% 50% at 50% 42%, rgba(5,5,5,0.15) 0%, rgba(5,5,5,0.72) 58%, rgba(5,5,5,0.96) 100%),
                linear-gradient(180deg, rgba(5,5,5,0.72) 0%, rgba(5,5,5,0.25) 28%, rgba(5,5,5,0.55) 70%, rgba(5,5,5,0.95) 100%);
        }

        .auth__wash {
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(ellipse 40% 30% at 18% 20%, rgba(201,165,90,0.16), transparent 60%),
                radial-gradient(ellipse 35% 28% at 82% 78%, rgba(201,165,90,0.08), transparent 65%);
            pointer-events: none;
        }

        .auth__grain {
            position: absolute;
            inset: 0;
            z-index: 0;
            opacity: 0.055;
            mix-blend-mode: overlay;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        /* Top bar */
        .auth__nav {
            position: relative;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.35rem clamp(1.25rem, 4vw, 3rem);
        }

        .auth__brand {
            font-family: var(--display);
            font-size: 1.3rem;
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        .auth__brand em {
            font-style: italic;
            color: var(--gold);
            font-weight: 400;
            margin-left: 0.2rem;
        }

        .auth__nav-link {
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--muted);
            transition: color 0.25s ease;
        }

        .auth__nav-link:hover { color: var(--gold-soft); }

        /* Center stage */
        .auth__stage {
            position: relative;
            z-index: 4;
            display: grid;
            place-items: center;
            padding: 0.5rem clamp(1.1rem, 4vw, 2rem) 3rem;
            min-height: calc(100svh - 5.5rem);
        }

        .auth__card {
            width: min(440px, 100%);
            padding: clamp(1.75rem, 4vw, 2.4rem);
            background:
                linear-gradient(165deg, rgba(20,20,20,0.72) 0%, rgba(8,8,8,0.82) 100%);
            border: 1px solid rgba(244, 244, 242, 0.1);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.04);
            animation: rise 0.95s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .auth__card::before {
            content: "";
            display: block;
            width: 2.25rem;
            height: 1px;
            margin-bottom: 1.15rem;
            background: var(--gold);
            transform-origin: left;
            animation: lineIn 0.9s ease 0.25s both;
        }

        .auth-panel__eyebrow {
            font-size: 0.66rem;
            font-weight: 500;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--gold);
        }

        .auth-panel__title {
            margin-top: 0.55rem;
            font-family: var(--display);
            font-size: clamp(2.15rem, 5vw, 2.85rem);
            font-weight: 500;
            line-height: 1.02;
            letter-spacing: -0.025em;
        }

        .auth-panel__lead {
            margin-top: 0.7rem;
            margin-bottom: 1.7rem;
            font-size: 0.95rem;
            font-weight: 300;
            line-height: 1.6;
            color: var(--muted);
        }

        /* Form */
        .auth-form .field { margin-top: 1.05rem; }
        .auth-form .field:first-of-type { margin-top: 0; }

        .auth-form label,
        .auth-form .block.font-medium {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.68rem !important;
            font-weight: 500 !important;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted) !important;
        }

        .auth-form input[type="text"],
        .auth-form input[type="email"],
        .auth-form input[type="password"] {
            width: 100%;
            min-height: 2.85rem;
            padding: 0.7rem 0.85rem;
            background: rgba(244, 244, 242, 0.035) !important;
            border: 1px solid var(--line) !important;
            border-radius: 0 !important;
            color: var(--fg) !important;
            font-size: 0.95rem;
            font-weight: 300;
            box-shadow: none !important;
            outline: none;
            transition: border-color 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
        }

        .auth-form input:focus {
            border-color: rgba(201, 165, 90, 0.75) !important;
            background: rgba(201, 165, 90, 0.06) !important;
            box-shadow: 0 0 0 3px rgba(201, 165, 90, 0.12) !important;
        }

        .auth-form .check {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            margin-top: 1.1rem;
            font-size: 0.88rem;
            font-weight: 300;
            color: var(--muted);
            cursor: pointer;
        }

        .auth-form .check input {
            width: 0.95rem;
            height: 0.95rem;
            accent-color: var(--gold);
        }

        .auth-form .actions {
            margin-top: 1.6rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .auth-form .link-muted {
            font-size: 0.84rem;
            font-weight: 300;
            color: var(--muted);
            border-bottom: 1px solid transparent;
            transition: color 0.25s ease, border-color 0.25s ease;
        }

        .auth-form .link-muted:hover {
            color: var(--gold-soft);
            border-bottom-color: rgba(201, 165, 90, 0.45);
        }

        .auth-form button[type="submit"],
        .auth-form .btn-gold {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.9rem;
            padding: 0 1.4rem;
            border: none !important;
            border-radius: 0 !important;
            background: var(--gold) !important;
            color: #050505 !important;
            font-size: 0.7rem !important;
            font-weight: 600 !important;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
            box-shadow: none !important;
        }

        .auth-form button[type="submit"]:hover,
        .auth-form .btn-gold:hover {
            background: var(--gold-soft) !important;
            transform: translateY(-1px);
            box-shadow: 0 10px 28px rgba(201, 165, 90, 0.22) !important;
        }

        .auth-form .text-red-600,
        .auth-form .text-sm.text-red-600 {
            color: var(--danger) !important;
            font-weight: 300;
            margin-top: 0.35rem;
        }

        .auth-form .text-green-600,
        .auth-form .font-medium.text-sm.text-green-600 {
            color: var(--ok) !important;
            font-weight: 400;
        }

        .auth-form .note {
            font-size: 0.92rem;
            font-weight: 300;
            line-height: 1.6;
            color: var(--muted);
            margin-bottom: 1.2rem;
        }

        .auth-panel__footer {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--faint);
            font-size: 0.88rem;
            font-weight: 300;
            color: var(--muted);
            text-align: center;
        }

        .auth-panel__footer a {
            color: var(--gold-soft);
            border-bottom: 1px solid rgba(201, 165, 90, 0.35);
            transition: border-color 0.25s ease;
        }

        .auth-panel__footer a:hover {
            border-bottom-color: var(--gold-soft);
        }

        .auth__footnote {
            position: absolute;
            left: clamp(1.25rem, 4vw, 3rem);
            bottom: 1.2rem;
            z-index: 5;
            font-size: 0.65rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(244, 244, 242, 0.35);
            pointer-events: none;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(22px) scale(0.985); }
            to { opacity: 1; transform: none; }
        }

        @keyframes lineIn {
            from { transform: scaleX(0); opacity: 0; }
            to { transform: scaleX(1); opacity: 1; }
        }

        @keyframes drift {
            from { transform: scale(1.08) translate3d(0, 0, 0); }
            to { transform: scale(1.14) translate3d(-1.2%, 1%, 0); }
        }

        @media (prefers-reduced-motion: reduce) {
            .auth__world img,
            .auth__card,
            .auth__card::before { animation: none; }
        }
    </style>
</head>
<body>
    <div class="auth">
        <div class="auth__world" aria-hidden="true">
            <img
                src="{{ asset('images/plano-bodega-niebla.jpg') }}"
                alt=""
                width="1920"
                height="1080"
            >
        </div>
        <div class="auth__veil" aria-hidden="true"></div>
        <div class="auth__wash" aria-hidden="true"></div>
        <div class="auth__grain" aria-hidden="true"></div>

        <header class="auth__nav">
            <a class="auth__brand" href="{{ url('/') }}">
                GoldenDrinks<em>GoldenSys</em>
            </a>
            <a class="auth__nav-link" href="{{ url('/') }}">Volver al inicio</a>
        </header>

        <main class="auth__stage">
            <div class="auth__card auth-form">
                {{ $slot }}
            </div>
        </main>

        <p class="auth__footnote">Campoalegre · Huila</p>
    </div>
</body>
</html>
