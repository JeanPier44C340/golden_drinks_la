<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GoldenSysSeeder extends Seeder
{
    /**
     * Datos semilla de BD/GoldenDrinks_Base_Datos.sql (sección 10).
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id' => 1, 'nombre' => 'administrador', 'descripcion' => 'Control total del sistema y de la operacion', 'activo' => true],
            ['id' => 2, 'nombre' => 'celador', 'descripcion' => 'Registra llegada y salida de vehiculos y alertas de ingreso', 'activo' => true],
            ['id' => 3, 'nombre' => 'bodeguero', 'descripcion' => 'Confirma descargas y registra danos de mercancia', 'activo' => true],
            ['id' => 4, 'nombre' => 'repartidor', 'descripcion' => 'Consulta inventario, despachos asignados y confirma entregas', 'activo' => true],
        ]);

        DB::table('usuarios')->insert([
            ['id' => 1, 'nombre_completo' => 'Administrador GoldenSys', 'correo' => 'admin@goldendrinks.local', 'password_hash' => '$2y$10$demo.hash.admin', 'telefono' => '3000000000', 'rol_id' => 1, 'estado' => 'activo'],
            ['id' => 2, 'nombre_completo' => 'Celador Principal', 'correo' => 'celador@goldendrinks.local', 'password_hash' => '$2y$10$demo.hash.celador', 'telefono' => '3000000001', 'rol_id' => 2, 'estado' => 'activo'],
            ['id' => 3, 'nombre_completo' => 'Bodeguero Principal', 'correo' => 'bodeguero@goldendrinks.local', 'password_hash' => '$2y$10$demo.hash.bodeguero', 'telefono' => '3000000002', 'rol_id' => 3, 'estado' => 'activo'],
            ['id' => 4, 'nombre_completo' => 'Carlos Perez (Repartidor)', 'correo' => 'repartidor@goldendrinks.local', 'password_hash' => '$2y$10$demo.hash.repartidor', 'telefono' => '3000000003', 'rol_id' => 4, 'estado' => 'activo'],
        ]);

        DB::table('proveedores')->insert([
            ['id' => 1, 'nit' => '900111222-1', 'nombre' => 'Licores del Valle S.A.', 'correo' => 'contacto@licoresdelvalle.co', 'password_hash' => Hash::make('password'), 'telefono' => '3101111111', 'direccion' => 'Cali, Valle', 'estado' => 'activo'],
            ['id' => 2, 'nit' => '901333444-2', 'nombre' => 'Distribuidora Andina Ltda.', 'correo' => 'ventas@andina.co', 'password_hash' => Hash::make('password'), 'telefono' => '3102222222', 'direccion' => 'Bogota, Cund.', 'estado' => 'activo'],
        ]);

        DB::table('vendedores')->insert([
            ['id' => 1, 'empresa' => 'Tienda La Esquina', 'nombre_contacto' => 'Marta Gomez', 'correo' => 'marta@laesquina.co', 'password_hash' => '$2y$10$demo.hash.vend1', 'telefono' => '3201111111', 'estado' => 'activo'],
            ['id' => 2, 'empresa' => 'Bar El Encuentro', 'nombre_contacto' => 'Luis Rojas', 'correo' => 'luis@elencuentro.co', 'password_hash' => '$2y$10$demo.hash.vend2', 'telefono' => '3202222222', 'estado' => 'activo'],
        ]);

        DB::table('categorias_producto')->insert([
            ['id' => 1, 'nombre' => 'Vino', 'descripcion' => 'Vinos tintos, blancos y rosados'],
            ['id' => 2, 'nombre' => 'Ron', 'descripcion' => 'Rones nacionales e importados'],
            ['id' => 3, 'nombre' => 'Aguardiente', 'descripcion' => 'Aguardientes anisados'],
            ['id' => 4, 'nombre' => 'Whisky', 'descripcion' => 'Whisky escoces y americano'],
        ]);

        // El trigger trg_producto_after_insert crea la fila de inventario en 0
        DB::table('productos')->insert([
            ['id' => 1, 'codigo' => 'VIN-001', 'nombre' => 'Vino Tinto Reserva 750ml', 'categoria_id' => 1, 'unidad_medida' => 'botella', 'precio_compra' => 18000, 'precio_distribucion' => 26000, 'stock_minimo' => 10, 'activo' => true],
            ['id' => 2, 'codigo' => 'RON-001', 'nombre' => 'Ron Medellin Anejo 750ml', 'categoria_id' => 2, 'unidad_medida' => 'botella', 'precio_compra' => 32000, 'precio_distribucion' => 45000, 'stock_minimo' => 12, 'activo' => true],
            ['id' => 3, 'codigo' => 'AGU-001', 'nombre' => 'Aguardiente Antioqueno 750ml', 'categoria_id' => 3, 'unidad_medida' => 'botella', 'precio_compra' => 25000, 'precio_distribucion' => 35000, 'stock_minimo' => 15, 'activo' => true],
            ['id' => 4, 'codigo' => 'WHI-001', 'nombre' => 'Whisky Buchanans 750ml', 'categoria_id' => 4, 'unidad_medida' => 'botella', 'precio_compra' => 95000, 'precio_distribucion' => 135000, 'stock_minimo' => 8, 'activo' => true],
        ]);

        DB::table('vehiculos')->insert([
            ['id' => 1, 'placa' => 'ABC-123', 'conductor' => 'Pedro Ramirez', 'tipo_vehiculo' => 'Camion NHR', 'capacidad_cajas' => 120, 'estado' => 'disponible', 'registrado_por_admin_id' => 1],
            ['id' => 2, 'placa' => 'XYZ-789', 'conductor' => 'Jorge Salas', 'tipo_vehiculo' => 'Turbo', 'capacidad_cajas' => 200, 'estado' => 'disponible', 'registrado_por_admin_id' => 1],
        ]);
    }
}
