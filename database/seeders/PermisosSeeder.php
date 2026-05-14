<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermisosSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            // Contactos
            'Ver contactos'      => 'Permite ver los contactos',
            'Editar contactos'   => 'Permite editar contactos',
            'Eliminar contactos' => 'Permite eliminar contactos',

            // Reclamaciones
            'Ver reclamaciones'      => 'Permite ver las reclamaciones',
            'Editar reclamaciones'   => 'Permite editar reclamaciones',
            'Eliminar reclamaciones' => 'Permite eliminar reclamaciones',

            // Modales
            'Ver modales'      => 'Permite ver los modales',
            'Editar modales'   => 'Permite editar modales',
            'Eliminar modales' => 'Permite eliminar modales',
            'Enviar mensajes'  => 'Enviar modales de Emails y WhatsApp',

            // Servicios (se puede descomentar en caso se implementen los servicios em el dashboard; las rutas ya están incluidas en el api.php)
            'Ver servicios' => 'Permite ver los servicios',
            // 'Crear servicios' => 'Permite crear nuevos servicios',
            // 'Editar servicios' => 'Permite editar servicios existentes',
            // 'Eliminar servicios' => 'Permite eliminar servicios existentes',

            // Roles
            'Ver roles'      => 'Permite ver la lista de roles',
            'Crear roles'    => 'Permite crear nuevos roles',
            'Editar roles'   => 'Permite modificar roles existentes',
            'Eliminar roles' => 'Permite eliminar roles existentes',

            // Permisos
            'Ver permisos'      => 'Permite ver la lista de permisos',
            'Crear permisos'    => 'Permite crear nuevos permisos',
            'Editar permisos'   => 'Permite modificar permisos existentes',
            'Eliminar permisos' => 'Permite eliminar permisos existentes',

            // Empleados
            'Ver empleados'      => 'Permite ver la lista de empleados',
            'Crear empleados'    => 'Permite crear nuevos empleados',
            'Editar empleados'   => 'Permite modificar empleados existentes',
            'Eliminar empleados' => 'Permite eliminar empleados existentes',

            // Clientes
            'Ver cliente'      => 'Permite ver la lista de clientes',
            'Crear cliente'    => 'Permite crear nuevos clientes',
            'Editar cliente'   => 'Permite modificar clientes existentes',
            'Eliminar cliente' => 'Permite eliminar clientes existentes',

            // Blogs
            'Ver blogs'      => 'Permite ver la gestión de blogs',
            'Crear blogs'    => 'Permite crear contenido de blogs',
            'Editar blogs'   => 'Permite editar contenido de blogs',
            'Eliminar blogs' => 'Permite eliminar contenido de blogs',

            // Tarjetas
            'Crear tarjetas'    => 'Permite crear tarjetas',
            'Eliminar tarjetas' => 'Permite eliminar tarjetas',

            // Propuestas
            'ver propuestas cliente' => 'Permite ver sus propuestas al cliente',
            'Ver propuestas'         => 'Permite  ver propuestas',
            'Crear propuestas'       => 'Permite crear propuestas',
            'Editar propuestas'      => 'Permite editar propuestas',
            'Eliminar propuestas'    => 'Permite eliminar propuestas',

            'Permisos generales' => 'Permite acceder a los permisos básicos',

            // Productos
            'Ver productos'      => 'Permite ver los productos',
            'Crear productos'    => 'Permite crear productos',
            'Editar productos'   => 'Permite editar productos',
            'Eliminar productos' => 'Permite eliminar productos',

            // Popups
            'Ver popups'      => 'Permite ver configuraciones de popups',
            'Crear popups'    => 'Permite crear configuraciones de popups',
            'Editar popups'   => 'Permite editar configuraciones de popups',
            'Eliminar popups' => 'Permite eliminar configuraciones de popups',
        ];

        foreach ($permisos as $nombre => $descripcion) {
            Permiso::updateOrCreate(
                ['nombre' => $nombre],
                ['slug' => Str::slug($nombre), 'descripcion' => $descripcion]
            );
        }

        $rolesPermisos = [
            'administrador' => array_keys($permisos), // todos
            'ventas'        => [
                'Ver contactos',
                'Editar contactos',

                'Ver modales',
                'Editar modales',

                'Ver reclamaciones',
                'Editar reclamaciones',

                'Enviar mensajes',
                'Permisos generales',

                /**
                 * Empleados de ventas podrán ver y gestionar propuestas a clientes
                 */

                // 'Crear propuestas',
                // 'Editar propuestas',
                // 'Eliminar propuestas',
                'Ver cliente',
                'Crear cliente',
                'Editar cliente',
                'Eliminar cliente',

                'Ver propuestas cliente',
                'Ver propuestas',
                'Crear propuestas',
                'Editar propuestas',
                'Eliminar propuestas',
            ],
            'marketing' => [
                'Ver contactos',
                'Editar contactos',

                'Ver modales',
                'Editar modales',

                'Ver reclamaciones',
                'Editar reclamaciones',

                'Enviar mensajes',

                'Ver blogs',
                'Editar blogs',
                'Eliminar blogs',
                'Crear blogs',
                'Crear tarjetas',

                'Ver popups',
                'Crear popups',
                'Editar popups',
                'Eliminar popups',

                'Permisos generales',
            ],
            'cliente' => [
                'Ver propuestas cliente',
                // 'Crear propuestas',
                // 'Editar propuestas',
                // 'Eliminar propuestas',
            ],
        ];

        foreach ($rolesPermisos as $nombreRol => $permisosAsignados) {
            $rol = Rol::firstOrCreate(['nombre' => $nombreRol]);

            $permisosIds = Permiso::whereIn('nombre', $permisosAsignados)->pluck('id_permiso')->toArray();

            $rol->permisos()->sync($permisosIds);
        }
    }
}
