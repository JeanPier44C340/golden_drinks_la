<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GoldenDrinks — GoldenSys</title>
    <meta name="description" content="GoldenSys: gestión digital de bodega para GoldenDrinks. Campoalegre, Huila.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #050505;
            --fg: #f4f4f2;
            --muted: rgba(244, 244, 242, 0.58);
            --faint: rgba(244, 244, 242, 0.14);
            --gold: #c9a55a;
            --gold-soft: #dfc07a;
            --display: "Cormorant Garamond", Georgia, serif;
            --sans: "Outfit", system-ui, sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { background: var(--bg); color: var(--fg); }
        body {
            font-family: var(--sans);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        img { display: block; width: 100%; height: 100%; object-fit: cover; }

        /* —— Cinema shell —— */
        .cinema {
            position: relative;
            height: 650vh;
        }

        .stage {
            position: sticky;
            top: 0;
            height: 100svh;
            width: 100%;
            overflow: hidden;
            isolation: isolate;
        }

        /* Background planes */
        .planes {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        .plane {
            position: absolute;
            inset: -8%;
            opacity: 0;
            will-change: opacity, transform, filter;
        }

        .plane img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(1) contrast(1.1) brightness(0.55);
        }

        .plane--a img { object-position: center 40%; }
        .plane--b img { object-position: center center; }
        .plane--c img { object-position: 60% center; }
        .plane--d img { object-position: center 30%; }

        .veil {
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background:
                radial-gradient(ellipse 80% 70% at 50% 45%, transparent 0%, rgba(5,5,5,0.35) 55%, rgba(5,5,5,0.92) 100%),
                linear-gradient(180deg, rgba(5,5,5,0.55) 0%, transparent 28%, transparent 62%, rgba(5,5,5,0.88) 100%);
            transition: background 0.6s ease;
        }

        .grain {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            opacity: 0.07;
            mix-blend-mode: overlay;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        .gold-wash {
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background: radial-gradient(ellipse 50% 40% at 70% 30%, rgba(201,165,90,0.12), transparent 60%);
            opacity: 0;
            will-change: opacity;
        }

        /* Top chrome */
        .chrome {
            position: absolute;
            inset: 0 0 auto;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.35rem clamp(1.25rem, 4vw, 3rem);
            mix-blend-mode: normal;
        }

        .chrome__brand {
            font-family: var(--display);
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        .chrome__brand i {
            font-style: italic;
            color: var(--gold);
            font-weight: 400;
        }

        .chrome__actions {
            display: flex;
            align-items: center;
            gap: 0.85rem 1.15rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .chrome__place {
            font-size: 0.68rem;
            font-weight: 500;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .chrome__link {
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--fg);
            opacity: 0.82;
            transition: color 0.25s ease, opacity 0.25s ease;
        }

        .chrome__link:hover {
            color: var(--gold-soft);
            opacity: 1;
        }

        .chrome__cta {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #050505;
            background: var(--gold);
            padding: 0.55rem 0.95rem;
            transition: background 0.25s ease, transform 0.25s ease;
        }

        .chrome__cta:hover {
            background: var(--gold-soft);
            transform: translateY(-1px);
        }

        @media (max-width: 640px) {
            .chrome__place { display: none; }
        }

        /* Progress rail */
        .rail {
            position: absolute;
            right: clamp(1rem, 3vw, 2.25rem);
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            width: 1px;
            height: min(42vh, 280px);
            background: var(--faint);
        }

        .rail__fill {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 0%;
            background: var(--gold);
            transform-origin: top;
            will-change: height;
        }

        /* Scene copy layers */
        .scenes {
            position: absolute;
            inset: 0;
            z-index: 5;
            display: grid;
            place-items: center;
            padding: clamp(5rem, 12vh, 7rem) clamp(1.25rem, 5vw, 4rem);
        }

        .scene {
            position: absolute;
            width: min(920px, 100%);
            opacity: 0;
            visibility: hidden;
            will-change: opacity, transform, filter;
            text-align: left;
        }

        .scene.is-center { text-align: center; margin-inline: auto; }
        .scene.is-center .scene__text { margin-inline: auto; }

        .scene__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.1rem;
            font-size: 0.68rem;
            font-weight: 500;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--gold);
        }

        .scene__eyebrow::before {
            content: "";
            width: 1.6rem;
            height: 1px;
            background: currentColor;
        }

        .scene.is-center .scene__eyebrow {
            justify-content: center;
        }

        .scene__title {
            font-family: var(--display);
            font-weight: 500;
            font-size: clamp(2.6rem, 7.5vw, 5.6rem);
            line-height: 0.95;
            letter-spacing: -0.03em;
            max-width: 12ch;
        }

        .scene.is-center .scene__title {
            max-width: 16ch;
            margin-inline: auto;
        }

        .scene__title em {
            font-style: italic;
            color: var(--gold-soft);
            font-weight: 400;
        }

        .scene__text {
            margin-top: 1.25rem;
            max-width: 36ch;
            font-size: clamp(0.98rem, 1.6vw, 1.12rem);
            font-weight: 300;
            line-height: 1.65;
            color: var(--muted);
        }

        .scene__tags {
            margin-top: 1.6rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem 1.1rem;
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--fg);
        }

        .scene.is-center .scene__tags { justify-content: center; }

        .scene__tags span {
            opacity: 0.85;
            border-bottom: 1px solid rgba(201, 165, 90, 0.4);
            padding-bottom: 0.2rem;
        }

        .scene__big {
            font-family: var(--display);
            font-size: clamp(4rem, 14vw, 9rem);
            font-weight: 500;
            line-height: 0.88;
            letter-spacing: -0.04em;
        }

        .scene__big span {
            display: block;
            font-style: italic;
            color: var(--gold);
            font-weight: 400;
        }

        /* Floating bottle accent (subtle, not the main UI) */
        .prop {
            position: absolute;
            z-index: 4;
            width: min(180px, 22vw);
            right: 8%;
            bottom: 8%;
            opacity: 0;
            pointer-events: none;
            will-change: transform, opacity, filter;
            filter: drop-shadow(0 30px 50px rgba(0,0,0,0.65));
        }

        @media (max-width: 820px) {
            .prop { display: none; }
            .rail { display: none; }
        }

        /* End panel after cinema */
        .after {
            position: relative;
            z-index: 2;
            background: var(--bg);
            border-top: 1px solid var(--faint);
            padding: clamp(4rem, 9vw, 7rem) clamp(1.25rem, 4vw, 3rem);
        }

        .after__grid {
            width: min(1100px, 100%);
            margin: 0 auto;
            display: grid;
            gap: 3rem;
        }

        @media (min-width: 900px) {
            .after__grid { grid-template-columns: 1.1fr 1fr; gap: 4rem; align-items: start; }
        }

        .after h2 {
            font-family: var(--display);
            font-size: clamp(2.2rem, 4.5vw, 3.4rem);
            font-weight: 500;
            line-height: 1.05;
            letter-spacing: -0.02em;
            max-width: 12ch;
        }

        .after p {
            margin-top: 1rem;
            max-width: 42ch;
            font-weight: 300;
            line-height: 1.7;
            color: var(--muted);
        }

        .roles {
            display: grid;
            gap: 0;
        }

        .role {
            padding: 1.15rem 0;
            border-top: 1px solid var(--faint);
            display: grid;
            gap: 0.35rem;
        }

        .role:last-child { border-bottom: 1px solid var(--faint); }

        .role strong {
            font-family: var(--display);
            font-size: 1.35rem;
            font-weight: 500;
        }

        .role span {
            font-size: 0.9rem;
            font-weight: 300;
            color: var(--muted);
            line-height: 1.5;
        }

        .metrics {
            margin-top: 2.5rem;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem 1.5rem;
        }

        .metric b {
            display: block;
            font-family: var(--display);
            font-size: 2.4rem;
            font-weight: 500;
            color: var(--gold);
            line-height: 1;
        }

        .metric small {
            display: block;
            margin-top: 0.4rem;
            font-size: 0.72rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
        }

        footer {
            padding: 1.4rem clamp(1.25rem, 4vw, 3rem) 2rem;
            border-top: 1px solid var(--faint);
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem 1.5rem;
            justify-content: space-between;
            font-size: 0.78rem;
            color: var(--muted);
            font-weight: 300;
        }

        footer strong {
            font-family: var(--display);
            color: var(--gold);
            font-weight: 500;
            font-size: 1rem;
        }

        .hint {
            position: absolute;
            left: 50%;
            bottom: 1.6rem;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.62rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--muted);
            opacity: 1;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }

        .hint__bar {
            width: 1px;
            height: 2.6rem;
            background: linear-gradient(180deg, var(--gold), transparent);
            animation: breath 2.4s ease-in-out infinite;
        }

        @keyframes breath {
            0%, 100% { opacity: 0.3; transform: scaleY(0.8); transform-origin: top; }
            50% { opacity: 1; transform: scaleY(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            .cinema { height: auto; }
            .stage { position: relative; height: auto; min-height: 100svh; }
            .scene { position: relative; opacity: 1 !important; visibility: visible !important; transform: none !important; filter: none !important; margin: 3rem 0; }
            .plane { opacity: 0.35 !important; transform: none !important; }
            .plane:first-child { opacity: 0.7 !important; }
            .prop, .hint, .rail { display: none; }
            .hint__bar { animation: none; }
        }
    </style>
</head>
<body>
    <div class="cinema" id="cinema" aria-label="Experiencia GoldenDrinks">
        <div class="stage" id="stage">
            <div class="planes" aria-hidden="true">
                <div class="plane plane--a" data-plane="0">
                    <img src="{{ asset('images/hero-botellas.jpg') }}" alt="" width="1920" height="1080">
                </div>
                <div class="plane plane--b" data-plane="1">
                    <img src="{{ asset('images/plano-bodega-niebla.jpg') }}" alt="" width="1920" height="1080">
                </div>
                <div class="plane plane--c" data-plane="2">
                    <img src="{{ asset('images/plano-cristal.jpg') }}" alt="" width="1920" height="1080">
                </div>
                <div class="plane plane--d" data-plane="3">
                    <img src="{{ asset('images/hero-goldendrinks.jpg') }}" alt="" width="1920" height="1080">
                </div>
            </div>

            <div class="veil" aria-hidden="true"></div>
            <div class="gold-wash" id="goldWash" aria-hidden="true"></div>
            <div class="grain" aria-hidden="true"></div>

            <header class="chrome">
                <a class="chrome__brand" href="{{ url('/') }}">GoldenDrinks <i>GoldenSys</i></a>
                <div class="chrome__actions">
                    <span class="chrome__place">Campoalegre · Huila</span>
                    @auth
                        <a class="chrome__link" href="{{ url('/dashboard') }}">Panel</a>
                    @else
                        <a class="chrome__link" href="{{ route('login') }}">Entrar</a>
                        <a class="chrome__link" href="{{ route('proveedor.login') }}">Portal proveedor</a>
                        <a class="chrome__cta" href="{{ route('register') }}">Registrarse</a>
                    @endauth
                </div>
            </header>

            <div class="rail" aria-hidden="true"><div class="rail__fill" id="railFill"></div></div>

            <img
                class="prop"
                id="prop"
                src="{{ asset('images/botella-goldendrinks.png') }}"
                alt=""
                width="400"
                height="700"
                aria-hidden="true"
            >

            <div class="scenes">
                {{-- Scene 0: opening brand --}}
                <article class="scene is-center" data-scene="0">
                    <p class="scene__eyebrow">Gestión digital de bodega</p>
                    <h1 class="scene__big">GoldenDrinks<span>GoldenSys</span></h1>
                    <p class="scene__text" style="max-width:28ch;margin-top:1.4rem">
                        La operación de bodega, contada en planos.
                    </p>
                </article>

                {{-- Scene 1: problem --}}
                <article class="scene" data-scene="1">
                    <p class="scene__eyebrow">El punto de partida</p>
                    <h2 class="scene__title">Cuando el stock vivía en <em>mensajes</em></h2>
                    <p class="scene__text">
                        Registros manuales, consultas verbales y poca trazabilidad entre recepción,
                        pérdidas y despachos. GoldenDrinks necesitaba una sola fuente de verdad.
                    </p>
                    <div class="scene__tags">
                        <span>Papel</span>
                        <span>Errores</span>
                        <span>Sin historial</span>
                    </div>
                </article>

                {{-- Scene 2: reception --}}
                <article class="scene" data-scene="2">
                    <p class="scene__eyebrow">Plano 01 · Recepción</p>
                    <h2 class="scene__title">Cada vehículo entra y <em>sale</em> con registro</h2>
                    <p class="scene__text">
                        Llegada, proveedor, descarga y salida. El celador y el bodeguero cierran el ciclo
                        con evidencia: buenas, dañadas y observaciones.
                    </p>
                    <div class="scene__tags">
                        <span>Celador</span>
                        <span>Bodeguero</span>
                        <span>Trazabilidad</span>
                    </div>
                </article>

                {{-- Scene 3: inventory --}}
                <article class="scene" data-scene="3">
                    <p class="scene__eyebrow">Plano 02 · Inventario</p>
                    <h2 class="scene__title">El stock se mueve <em>solo</em></h2>
                    <p class="scene__text">
                        Entradas, salidas y pérdidas actualizan el inventario en tiempo real.
                        Si baja del mínimo, la alerta aparece sin pedirlo.
                    </p>
                    <div class="scene__tags">
                        <span>Kardex</span>
                        <span>Alertas</span>
                        <span>Tiempo real</span>
                    </div>
                </article>

                {{-- Scene 4: commercial --}}
                <article class="scene" data-scene="4">
                    <p class="scene__eyebrow">Plano 03 · Comercial</p>
                    <h2 class="scene__title">Pedidos con pago <em>verificado</em></h2>
                    <p class="scene__text">
                        Portales para proveedores y vendedores. Catálogo, comprobante, aprobación
                        y despacho — sin atajos: la regla de negocio sostiene la operación.
                    </p>
                    <div class="scene__tags">
                        <span>Portal proveedor</span>
                        <span>Portal vendedor</span>
                        <span>RN-15</span>
                    </div>
                </article>

                {{-- Scene 5: close --}}
                <article class="scene is-center" data-scene="5">
                    <p class="scene__eyebrow">Cierre</p>
                    <h2 class="scene__title" style="max-width:14ch;margin-inline:auto">De la bodega al <em>cliente</em></h2>
                    <p class="scene__text">
                        Despacho a repartidor, descuento de inventario y evidencia fotográfica de entrega.
                        Un sistema serio para una operación que no puede improvisar.
                    </p>
                    <div class="scene__tags">
                        <span>Campoalegre</span>
                        <span>Huila</span>
                        <span>2026</span>
                    </div>
                </article>
            </div>

            <div class="hint" id="hint" aria-hidden="true">
                <span class="hint__bar"></span>
                <span>Desplazar</span>
            </div>
        </div>
    </div>

    <section class="after" id="detalle">
        <div class="after__grid">
            <div>
                <h2>Seis roles. Una bodega bajo control.</h2>
                <p>
                    GoldenSys centraliza recepciones, inventario, despachos, alertas, reportes PDF
                    y la relación con proveedores y vendedores externos para GoldenDrinks.
                </p>
                <div class="metrics">
                    <div class="metric"><b>25</b><small>Requerimientos</small></div>
                    <div class="metric"><b>6</b><small>Roles</small></div>
                    <div class="metric"><b>7</b><small>Módulos</small></div>
                    <div class="metric"><b>1</b><small>Fuente de verdad</small></div>
                </div>
            </div>
            <div class="roles">
                <div class="role"><strong>Administrador</strong><span>Dashboard, usuarios, pagos, despachos y reportes.</span></div>
                <div class="role"><strong>Celador</strong><span>Llegada y salida de vehículos e historial.</span></div>
                <div class="role"><strong>Bodeguero</strong><span>Descargas, dañados e inventario operativo.</span></div>
                <div class="role"><strong>Repartidor</strong><span>Despachos asignados y foto de entrega.</span></div>
                <div class="role"><strong>Proveedor</strong><span>Órdenes, estados, daños y facturación.</span></div>
                <div class="role"><strong>Vendedor</strong><span>Catálogo, pedidos, pago y reclamos.</span></div>
            </div>
        </div>
    </section>

    <footer>
        <p><strong>GoldenDrinks</strong> · GoldenSys</p>
        <p>Campoalegre, Huila · Gestión digital de bodega</p>
    </footer>

    <script>
        (() => {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce) return;

            const cinema = document.getElementById('cinema');
            const planes = [...document.querySelectorAll('.plane')];
            const scenes = [...document.querySelectorAll('.scene')];
            const prop = document.getElementById('prop');
            const rail = document.getElementById('railFill');
            const wash = document.getElementById('goldWash');
            const hint = document.getElementById('hint');

            // Scene timeline: [start, peak, end] in 0..1 progress
            // Planes map roughly to visual chapters
            const sceneWindows = [
                [0.00, 0.06, 0.14],
                [0.12, 0.22, 0.32],
                [0.28, 0.40, 0.50],
                [0.46, 0.58, 0.68],
                [0.64, 0.76, 0.86],
                [0.82, 0.92, 1.00],
            ];

            // Which background plane dominates per progress band
            const planeKeys = [
                { at: 0.00, i: 0 },
                { at: 0.18, i: 1 },
                { at: 0.38, i: 2 },
                { at: 0.58, i: 3 },
                { at: 0.78, i: 1 },
                { at: 0.92, i: 0 },
            ];

            const clamp = (v, a = 0, b = 1) => Math.min(b, Math.max(a, v));
            const lerp = (a, b, t) => a + (b - a) * t;
            const smooth = (t) => t * t * (3 - 2 * t);

            function windowWeight(p, start, peak, end) {
                if (p <= start || p >= end) return 0;
                if (p <= peak) return smooth((p - start) / Math.max(0.0001, peak - start));
                return smooth((end - p) / Math.max(0.0001, end - peak));
            }

            function planeMix(progress) {
                // Soft crossfade between keyed planes
                const weights = new Array(planes.length).fill(0);
                for (let k = 0; k < planeKeys.length; k++) {
                    const cur = planeKeys[k];
                    const next = planeKeys[k + 1] || { at: 1.05, i: cur.i };
                    if (progress >= cur.at && progress <= next.at) {
                        const t = smooth((progress - cur.at) / Math.max(0.0001, next.at - cur.at));
                        weights[cur.i] += 1 - t;
                        weights[next.i] += t;
                        break;
                    }
                }
                // normalize
                const sum = weights.reduce((a, b) => a + b, 0) || 1;
                return weights.map((w) => w / sum);
            }

            let ticking = false;

            function render() {
                ticking = false;
                const rect = cinema.getBoundingClientRect();
                const total = cinema.offsetHeight - window.innerHeight;
                const progress = clamp((-rect.top) / Math.max(1, total));

                if (hint) hint.style.opacity = progress < 0.04 ? '1' : '0';
                if (rail) rail.style.height = `${progress * 100}%`;
                if (wash) wash.style.opacity = String(0.15 + Math.sin(progress * Math.PI) * 0.55);

                // Planes: opacity + ken burns
                const mix = planeMix(progress);
                planes.forEach((plane, i) => {
                    const o = mix[i];
                    const drift = (progress + i * 0.12) % 1;
                    const scale = 1.05 + drift * 0.08;
                    const y = (drift - 0.5) * 6;
                    const x = Math.sin((progress + i) * Math.PI * 2) * 1.5;
                    plane.style.opacity = String(o);
                    plane.style.transform = `translate3d(${x}%, ${y}%, 0) scale(${scale})`;
                    plane.style.filter = `brightness(${0.85 + o * 0.2})`;
                });

                // Scenes
                scenes.forEach((scene, i) => {
                    const [start, peak, end] = sceneWindows[i];
                    const w = windowWeight(progress, start, peak, end);
                    const visible = w > 0.02;
                    scene.style.visibility = visible ? 'visible' : 'hidden';
                    scene.style.opacity = String(w);
                    const rise = (1 - w) * 36;
                    const blur = (1 - w) * 8;
                    scene.style.transform = `translate3d(0, ${rise}px, 0)`;
                    scene.style.filter = blur > 0.4 ? `blur(${blur}px)` : 'none';
                });

                // Bottle prop — appears mid journey, drifts, exits
                if (prop) {
                    const appear = windowWeight(progress, 0.22, 0.45, 0.88);
                    const spin = lerp(-12, 8, progress);
                    const y = lerp(40, -30, progress);
                    const x = Math.sin(progress * Math.PI * 2) * 8;
                    const s = lerp(0.85, 1.08, window);
                    prop.style.opacity = String(appear * 0.95);
                    prop.style.transform = `translate3d(${x}%, ${y}px, 0) rotate(${spin}deg) scale(${s})`;
                }
            }

            function onScroll() {
                if (!ticking) {
                    ticking = true;
                    requestAnimationFrame(render);
                }
            }

            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', onScroll, { passive: true });
            render();
        })();
    </script>
</body>
</html>
