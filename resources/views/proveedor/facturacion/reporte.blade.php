<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Facturación · {{ $proveedor->nombre }}</title>
    <style>
        body { font-family: Georgia, serif; color: #111; margin: 2rem; }
        h1 { font-size: 1.6rem; margin: 0 0 .3rem; }
        .meta { color: #555; margin-bottom: 1.5rem; font-size: .95rem; }
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th, td { border-bottom: 1px solid #ddd; padding: .55rem .4rem; text-align: left; }
        th { font-size: .7rem; letter-spacing: .08em; text-transform: uppercase; color: #666; }
        .totales { margin-top: 1.4rem; }
        .totales strong { display: inline-block; min-width: 12rem; }
        .empty { color: #777; font-style: italic; margin-top: 1rem; }
    </style>
</head>
<body>
    <h1>Resumen de facturación</h1>
    <p class="meta">
        {{ $proveedor->nombre }} · NIT {{ $proveedor->nit }}<br>
        Período {{ $periodo_desde }} a {{ $periodo_hasta }}<br>
        Generado {{ $generado_en->format('d/m/Y H:i') }} · GoldenDrinks GoldenSys
    </p>

    @if ($lineas->isEmpty())
        <p class="empty">Sin entregas registradas en el período seleccionado.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Recepción</th>
                    <th>Producto</th>
                    <th>Recibida</th>
                    <th>Dañada</th>
                    <th>Buenas</th>
                    <th>Valor est.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lineas as $l)
                    @php $buenas = max(0, $l->cantidad_recibida - $l->cantidad_danada); @endphp
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($l->confirmada_en)->format('d/m/Y') }}</td>
                        <td>{{ $l->codigo_recepcion }}</td>
                        <td>{{ $l->codigo }} · {{ $l->nombre }}</td>
                        <td>{{ $l->cantidad_recibida }}</td>
                        <td>{{ $l->cantidad_danada }}</td>
                        <td>{{ $buenas }}</td>
                        <td>{{ number_format($buenas * (float) $l->precio_compra, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="totales">
        <p><strong>Unidades recibidas:</strong> {{ $totales['recibidas'] }}</p>
        <p><strong>Unidades dañadas:</strong> {{ $totales['danadas'] }}</p>
        <p><strong>Unidades buenas:</strong> {{ $totales['buenas'] }}</p>
        <p><strong>Valor estimado:</strong> $ {{ number_format($totales['valor_estimado'], 0, ',', '.') }}</p>
    </div>
</body>
</html>
