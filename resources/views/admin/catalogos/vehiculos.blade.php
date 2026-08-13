<x-admin-layout title="Vehículos">
    <header class="page-head">
        <p class="page-head__eyebrow">Catálogos</p>
        <h1 class="page-head__title">Vehículos</h1>
        <p class="page-head__lead">Flota registrada para recepciones y ciclo en bodega.</p>
    </header>

    <section class="panel">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Conductor</th>
                        <th>Tipo</th>
                        <th>Capacidad</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vehiculos as $v)
                        <tr>
                            <td><strong>{{ $v->placa }}</strong></td>
                            <td>{{ $v->conductor }}</td>
                            <td>{{ $v->tipo_vehiculo }}</td>
                            <td>{{ $v->capacidad_cajas }} cajas</td>
                            <td><span class="badge">{{ $v->estado }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
