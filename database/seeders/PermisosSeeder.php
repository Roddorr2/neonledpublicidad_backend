<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Support\Str;

class PermisosSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            // Contactos
            'Ver contactos' => 'Permite ver los contactos',
            'Editar contactos' => 'Permite editar contactos',
            'Eliminar contactos' => 'Permite eliminar contactos',

            // Reclamaciones
            'Ver reclamaciones' => 'Permite ver las reclamaciones',
            'Editar reclamaciones' => 'Permite editar reclamaciones',
            'Eliminar reclamaciones' => 'Permite eliminar reclamaciones',

            // Modales
            'Ver modales' => 'Permite ver los modales',
            'Editar modales' => 'Permite editar modales',
            'Eliminar modales' => 'Permite eliminar modales',
            'Enviar mensajes' => 'Enviar modales de Emails y WhatsApp',

            // Pop-Ups
            'Ver pop-ups' => 'Permite ver configuraciones de pop-ups',
            'Crear pop-ups' => 'Permite crear nuevos pop-ups',
            'Editar pop-ups' => 'Permite editar pop-ups existentes',
            'Eliminar pop-ups' => 'Permite eliminar pop-ups',

            // Servicios
            'Ver servicios' => 'Permite ver los servicios',

            // Productos
            'Ver productos' => 'Permite ver los productos',

            // Roles
            'Ver roles' => 'Permite ver la lista de roles',
            'Crear roles' => 'Permite crear nuevos roles',
            'Editar roles' => 'Permite modificar roles existentes',
            'Eliminar roles' => 'Permite eliminar roles existentes',

            // Permisos
            'Ver permisos' => 'Permite ver la lista de permisos',
            'Crear permisos' => 'Permite crear nuevos permisos',
            'Editar permisos' => 'Permite modificar permisos existentes',
            'Eliminar permisos' => 'Permite eliminar permisos existentes',

            // Empleados
            'Ver empleados' => 'Permite ver la lista de empleados',
            'Crear empleados' => 'Permite crear nuevos empleados',
            'Editar empleados' => 'Permite modificar empleados existentes',
            'Eliminar empleados' => 'Permite eliminar empleados existentes',

            // Blogs
            'Ver blogs' => 'Permite ver la gestión de blogs',
            'Crear blogs' => 'Permite crear contenido de blogs',
            'Editar blogs' => 'Permite editar contenido de blogs',
            'Eliminar blogs' => 'Permite eliminar contenido de blogs',

            // Tarjetas
            'Crear tarjetas' => 'Permite crear tarjetas',
            'Eliminar tarjetas' => 'Permite eliminar tarjetas',

            'Permisos generales' => 'Permite acceder a los permisos básicos',
        ];

        // Crear/actualizar permisos (idempotente)
        foreach ($permisos as $nombre => $descripcion) {
            Permiso::updateOrCreate(
                ['nombre' => $nombre],
                ['slug' => Str::slug($nombre), 'descripcion' => $descripcion]
            );
        }

        $rolesPermisos = [
            'administrador' => array_keys($permisos),
            
            'ventas' => [
                'Ver contactos',
                'Editar contactos',
                'Ver modales',
                'Editar modales',
                'Ver reclamaciones',
                'Editar reclamaciones',
                'Enviar mensajes',
                'Ver productos',
                'Permisos generales',
            ],
            
            'marketing' => [
                'Ver contactos',
                'Editar contactos',
                'Ver productos',
                'Ver modales',
                'Editar modales',
                'Ver reclamaciones',
                'Editar reclamaciones',
                'Enviar mensajes',
                'Ver pop-ups',
                'Crear pop-ups',
                'Editar pop-ups',
                'Eliminar pop-ups',
                'Ver blogs',
                'Editar blogs',
                'Eliminar blogs',
                'Crear blogs',
                'Crear tarjetas',
                'Permisos generales',
            ],
        ];

        // Asignar permisos a roles (idempotente con syncWithoutDetaching)
        foreach ($rolesPermisos as $nombreRol => $permisosAsignados) {
            $rol = Rol::firstOrCreate(['nombre' => $nombreRol]);

            $permisosIds = Permiso::whereIn('nombre', $permisosAsignados)->pluck('id_permiso')->toArray();

            // syncWithoutDetaching NO elimina permisos existentes, solo agrega los que faltan
            $rol->permisos()->syncWithoutDetaching($permisosIds);
        }

        $this->command->info('Permisos y roles configurados correctamente.');
    }
}