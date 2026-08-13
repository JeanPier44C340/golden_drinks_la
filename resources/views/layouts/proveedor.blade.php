<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Proveedor' }} · GoldenSys</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400;1,500&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #050505;
            --panel: rgba(16, 16, 16, 0.88);
            --fg: #f4f4f2;
            --muted: rgba(244, 244, 242, 0.58);
            --faint: rgba(244, 244, 242, 0.1);
            --line: rgba(244, 244, 242, 0.16);
            --gold: #c9a55a;
            --gold-soft: #dfc07a;
            --danger: #e08a8a;
            --ok: #9ecf9a;
            --warn: #e0c28a;
            --display: "Cormorant Garamond", Georgia, serif;
            --sans: "Outfit", system-ui, sans-serif;
            --side: 16.5rem;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100svh;
            font-family: var(--sans);
            color: var(--fg);
            background: var(--bg);
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; color: inherit; }

        .shell {
            min-height: 100svh;
            display: grid;
            grid-template-columns: var(--side) 1fr;
            isolation: isolate;
        }

        .shell__world {
            position: fixed;
            inset: 0;
            z-index: -3;
            overflow: hidden;
        }
        .shell__world img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(1) contrast(1.1) brightness(0.28);
            transform: scale(1.06);
        }
        .shell__veil {
            position: fixed;
            inset: 0;
            z-index: -2;
            background:
                radial-gradient(ellipse 50% 40% at 10% 0%, rgba(201,165,90,0.12), transparent 55%),
                linear-gradient(90deg, rgba(5,5,5,0.96) 0%, rgba(5,5,5,0.88) 40%, rgba(5,5,5,0.82) 100%);
        }

        .side {
            position: sticky;
            top: 0;
            height: 100svh;
            padding: 1.4rem 1rem 1.2rem;
            border-right: 1px solid var(--faint);
            background: rgba(5,5,5,0.55);
            backdrop-filter: blur(14px);
            display: flex;
            flex-direction: column;
            gap: 1.4rem;
            overflow-y: auto;
        }

        .side__brand {
            font-family: var(--display);
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            padding: 0 0.55rem;
        }
        .side__brand em {
            display: block;
            margin-top: 0.15rem;
            font-style: italic;
            font-weight: 400;
            color: var(--gold);
            font-size: 0.95rem;
        }

        .side__nav {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            flex: 1;
        }
        .side__group {
            margin-top: 0.85rem;
            margin-bottom: 0.35rem;
            padding: 0 0.55rem;
            font-size: 0.62rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(244,244,242,0.35);
        }
        .side__link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.62rem 0.7rem;
            font-size: 0.88rem;
            font-weight: 400;
            color: var(--muted);
            border-left: 2px solid transparent;
            transition: color .2s ease, background .2s ease, border-color .2s ease;
        }
        .side__link:hover { color: var(--fg); background: rgba(244,244,242,0.04); }
        .side__link.is-active {
            color: var(--gold-soft);
            border-left-color: var(--gold);
            background: rgba(201,165,90,0.08);
        }

        .side__user {
            padding: 0.9rem 0.7rem 0.2rem;
            border-top: 1px solid var(--faint);
        }
        .side__user strong {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .side__user span {
            display: block;
            margin-top: 0.2rem;
            font-size: 0.75rem;
            color: var(--muted);
        }
        .side__logout {
            margin-top: 0.75rem;
            display: inline-flex;
            font-size: 0.68rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
            background: none;
            border: none;
            cursor: pointer;
        }
        .side__logout:hover { color: var(--gold-soft); }

        .main {
            min-width: 0;
            padding: 1.5rem clamp(1.1rem, 3vw, 2.4rem) 2.5rem;
        }

        .page-head {
            margin-bottom: 1.6rem;
            animation: rise .7s cubic-bezier(.22,1,.36,1) both;
        }
        .page-head__eyebrow {
            font-size: 0.66rem;
            font-weight: 500;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--gold);
        }
        .page-head__title {
            margin-top: 0.4rem;
            font-family: var(--display);
            font-size: clamp(2rem, 4vw, 2.7rem);
            font-weight: 500;
            letter-spacing: -0.02em;
            line-height: 1.05;
        }
        .page-head__lead {
            margin-top: 0.55rem;
            max-width: 42rem;
            font-size: 0.95rem;
            font-weight: 300;
            color: var(--muted);
            line-height: 1.55;
        }
        .page-head__actions {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
        }

        .flash {
            margin-bottom: 1rem;
            padding: 0.85rem 1rem;
            border: 1px solid rgba(158,207,154,0.35);
            background: rgba(158,207,154,0.08);
            color: var(--ok);
            font-size: 0.9rem;
        }
        .flash--err {
            border-color: rgba(224,138,138,0.35);
            background: rgba(224,138,138,0.08);
            color: var(--danger);
        }

        .kpi-strip {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.85rem;
            margin-bottom: 1.25rem;
            animation: rise .8s cubic-bezier(.22,1,.36,1) .05s both;
        }
        .kpi {
            padding: 1.05rem 1.1rem;
            border: 1px solid var(--line);
            background: linear-gradient(165deg, rgba(24,24,24,0.72), rgba(8,8,8,0.78));
        }
        .kpi__label {
            font-size: 0.64rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .kpi__value {
            margin-top: 0.45rem;
            font-family: var(--display);
            font-size: 2rem;
            line-height: 1;
            color: var(--gold-soft);
        }
        .kpi__hint {
            margin-top: 0.45rem;
            font-size: 0.78rem;
            color: var(--muted);
            font-weight: 300;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 1rem;
        }
        .panel {
            border: 1px solid var(--line);
            background: var(--panel);
            padding: 1.15rem 1.2rem 1.25rem;
            animation: rise .85s cubic-bezier(.22,1,.36,1) .1s both;
        }
        .panel__head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.95rem;
            padding-bottom: 0.7rem;
            border-bottom: 1px solid var(--faint);
        }
        .panel__title {
            font-family: var(--display);
            font-size: 1.35rem;
            font-weight: 500;
        }
        .panel__meta {
            font-size: 0.72rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .table-wrap { overflow-x: auto; }
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        table.data th {
            text-align: left;
            font-size: 0.64rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 500;
            padding: 0.55rem 0.45rem;
            border-bottom: 1px solid var(--line);
        }
        table.data td {
            padding: 0.72rem 0.45rem;
            border-bottom: 1px solid var(--faint);
            font-weight: 300;
            vertical-align: middle;
        }
        table.data tr:hover td { background: rgba(244,244,242,0.025); }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 1.45rem;
            padding: 0 0.55rem;
            border: 1px solid var(--line);
            font-size: 0.68rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .badge--ok { color: var(--ok); border-color: rgba(158,207,154,0.4); }
        .badge--warn { color: var(--warn); border-color: rgba(224,194,138,0.4); }
        .badge--danger { color: var(--danger); border-color: rgba(224,138,138,0.4); }
        .badge--gold { color: var(--gold-soft); border-color: rgba(201,165,90,0.45); }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.5rem;
            padding: 0 1.05rem;
            border: 1px solid var(--line);
            background: transparent;
            color: var(--fg);
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background .2s ease, border-color .2s ease, transform .2s ease;
        }
        .btn:hover { border-color: rgba(201,165,90,0.55); transform: translateY(-1px); }
        .btn-gold {
            border: none;
            background: var(--gold);
            color: #050505;
        }
        .btn-gold:hover { background: var(--gold-soft); }
        .btn-danger { color: var(--danger); border-color: rgba(224,138,138,0.45); }
        .btn-sm { min-height: 2rem; padding: 0 0.75rem; font-size: 0.62rem; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem 1rem;
        }
        .field { display: flex; flex-direction: column; gap: 0.35rem; }
        .field--full { grid-column: 1 / -1; }
        .field label {
            font-size: 0.64rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .field input, .field select, .field textarea {
            min-height: 2.6rem;
            padding: 0.65rem 0.75rem;
            background: rgba(244,244,242,0.035);
            border: 1px solid var(--line);
            outline: none;
        }
        .field textarea { min-height: 6rem; resize: vertical; }
        .field input:focus, .field select:focus, .field textarea:focus {
            border-color: rgba(201,165,90,0.7);
            box-shadow: 0 0 0 3px rgba(201,165,90,0.12);
        }

        .empty {
            padding: 1.4rem 0.2rem;
            color: var(--muted);
            font-weight: 300;
            font-size: 0.92rem;
        }

        .list-soft { list-style: none; display: flex; flex-direction: column; gap: 0.75rem; }
        .list-soft li {
            display: grid;
            gap: 0.2rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--faint);
        }
        .list-soft li:last-child { border-bottom: none; padding-bottom: 0; }
        .list-soft strong { font-weight: 500; }
        .list-soft span { color: var(--muted); font-size: 0.84rem; font-weight: 300; }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
            margin-bottom: 1rem;
            align-items: end;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: none; }
        }

        @media (max-width: 1100px) {
            .kpi-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .grid-2 { grid-template-columns: 1fr; }
        }
        @media (max-width: 860px) {
            .shell { grid-template-columns: 1fr; }
            .side {
                position: relative;
                height: auto;
                border-right: none;
                border-bottom: 1px solid var(--faint);
            }
            .side__nav { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .side__group { grid-column: 1 / -1; }
            .form-grid { grid-template-columns: 1fr; }
        }
        @media (prefers-reduced-motion: reduce) {
            .page-head, .kpi-strip, .panel { animation: none; }
        }
    </style>
</head>
<body>
@php
    $nav = [
        ['group' => 'Portal', 'items' => [
            ['route' => 'proveedor.entregas.index', 'label' => 'Mis Entregas', 'match' => 'proveedor.entregas.*'],
            ['route' => 'proveedor.ordenes.create', 'label' => 'Nueva orden', 'match' => 'proveedor.ordenes.*'],
            ['route' => 'proveedor.notificaciones.index', 'label' => 'Notificaciones', 'match' => 'proveedor.notificaciones.*'],
            ['route' => 'proveedor.facturacion.index', 'label' => 'Facturación', 'match' => 'proveedor.facturacion.*'],
        ]],
    ];
@endphp
<div class="shell">
    <div class="shell__world" aria-hidden="true">
        <img src="{{ asset('images/plano-bodega-niebla.jpg') }}" alt="">
    </div>
    <div class="shell__veil" aria-hidden="true"></div>

    <aside class="side">
        <a class="side__brand" href="{{ route('proveedor.entregas.index') }}">
            GoldenDrinks
            <em>Proveedor · GoldenSys</em>
        </a>

        <nav class="side__nav" aria-label="Módulos proveedor">
            @foreach ($nav as $block)
                <div class="side__group">{{ $block['group'] }}</div>
                @foreach ($block['items'] as $item)
                    <a
                        class="side__link {{ request()->routeIs($item['match']) ? 'is-active' : '' }}"
                        href="{{ route($item['route']) }}"
                    >{{ $item['label'] }}</a>
                @endforeach
            @endforeach
        </nav>

        <div class="side__user">
            <strong>{{ Auth::guard('proveedor')->user()->nombre }}</strong>
            <span>{{ Auth::guard('proveedor')->user()->correo }} · Proveedor</span>
            <form method="POST" action="{{ route('proveedor.logout') }}">
                @csrf
                <button class="side__logout" type="submit">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <main class="main">
        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="flash flash--err">
                {{ $errors->first() }}
            </div>
        @endif

        {{ $slot }}
    </main>
</div>
</body>
</html>
