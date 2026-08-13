<x-admin-layout title="Proveedores">
    <header class="page-head">
        <p class="page-head__eyebrow">Catálogos</p>
        <h1 class="page-head__title">Proveedores</h1>
        <p class="page-head__lead">Empresas que abastecen la bodega GoldenDrinks.</p>
    </header>

    <section class="panel">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>NIT</th>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proveedores as $p)
                        <tr>
                            <td>{{ $p->nit }}</td>
                            <td>{{ $p->nombre }}</td>
                            <td>{{ $p->correo }}</td>
                            <td>{{ $p->telefono }}</td>
                            <td><span class="badge badge--gold">{{ $p->estado }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
