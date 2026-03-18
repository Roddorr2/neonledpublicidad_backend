<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

/**
 * Esquemas reutilizables para documentación de Autenticación
 * 
 * Este archivo contiene definiciones de esquemas OpenAPI comunes
 * que pueden ser referenciados desde los controladores para evitar repetición.
 */

//#[OA\Schema(
//    schema: 'LoginRequest',
//    type: 'object',
//    required: ['email', 'password'],
//    properties: [
//        new OA\Property(
//            property: 'email',
//            type: 'string',
//            format: 'email',
//            example: 'user@example.com',
//            description: 'Email del usuario'
//        ),
//        new OA\Property(
//            property: 'password',
//            type: 'string',
//            format: 'password',
//            example: 'password123',
//            description: 'Contraseña del usuario'
//        ),
//    ]
//)]
//class LoginRequest {}

//#[OA\Schema(
//    schema: 'RegisterRequest',
//    type: 'object',
//    required: ['nombre', 'apellido', 'email', 'dni', 'id_rol'],
//    properties: [
//        new OA\Property(
//            property: 'nombre',
//            type: 'string',
//            example: 'Juan',
//            description: 'Nombre del empleado'
//        ),
//        new OA\Property(
//            property: 'apellido',
//            type: 'string',
//            example: 'Pérez',
//            description: 'Apellido del empleado'
//        ),
//        new OA\Property(
//            property: 'email',
//            type: 'string',
//            format: 'email',
//            example: 'juan@example.com',
//            description: 'Email único del empleado'
//        ),
//        new OA\Property(
//            property: 'dni',
//            type: 'string',
//            maxLength: 20,
//            example: '12345678',
//            description: 'DNI único del empleado'
//        ),
//        new OA\Property(
//            property: 'telefono',
//            type: 'string',
//            maxLength: 20,
//            example: '+34 123 456 789',
//            description: 'Teléfono del empleado (opcional)'
//        ),
//        new OA\Property(
//            property: 'id_rol',
//            type: 'integer',
//            example: 2,
//            description: 'ID del rol a asignar'
//        ),
//    ]
//)]
//class RegisterRequest {}

//#[OA\Schema(
//    schema: 'ChangePasswordRequest',
//    type: 'object',
//    required: ['currentPassword', 'newPassword'],
//    properties: [
//        new OA\Property(
//            property: 'currentPassword',
//            type: 'string',
//            format: 'password',
//            example: 'currentPass123',
//            description: 'Contraseña actual del usuario'
//        ),
//        new OA\Property(
//            property: 'newPassword',
//            type: 'string',
//            format: 'password',
//            minLength: 8,
//            example: 'newPass123456',
//            description: 'Nueva contraseña (mínimo 8 caracteres)'
//        ),
//    ]
//)]
//class ChangePasswordRequest {}

//#[OA\Schema(
//    schema: 'ResetPasswordRequest',
//    type: 'object',
//    required: ['email'],
//    properties: [
//        new OA\Property(
//            property: 'email',
//            type: 'string',
//            format: 'email',
//            example: 'user@example.com',
//            description: 'Email del usuario para reset'
//        ),
//    ]
//)]
//class ResetPasswordRequest {}
//
//#[OA\Schema(
//    schema: 'UpdatePasswordRequest',
//    type: 'object',
//    required: ['token', 'password', 'password_confirmation'],
//    properties: [
//        new OA\Property(
//            property: 'token',
//            type: 'string',
//            example: 'abc123token456',
//            description: 'Token enviado por email para reset'
//        ),
//        new OA\Property(
//            property: 'password',
//            type: 'string',
//            format: 'password',
//            minLength: 6,
//            example: 'newPassword123',
//            description: 'Nueva contraseña'
//        ),
//        new OA\Property(
//            property: 'password_confirmation',
//            type: 'string',
//            format: 'password',
//            minLength: 6,
//            example: 'newPassword123',
//            description: 'Confirmación de nueva contraseña (debe coincidir)'
//        ),
//    ]
//)]
//class UpdatePasswordRequest {}

//#[OA\Schema(
//    schema: 'AuthSuccessResponse',
//    type: 'object',
//    properties: [
//        new OA\Property(
//            property: 'status',
//            type: 'string',
//            example: 'success',
//            description: 'Estado de la operación'
//        ),
//        new OA\Property(
//            property: 'message',
//            type: 'string',
//            example: 'Operación exitosa',
//            description: 'Mensaje descriptivo'
//        ),
//        new OA\Property(
//            property: 'user',
//            type: 'object',
//            properties: [
//                new OA\Property(property: 'id', type: 'integer', example: 1),
//                new OA\Property(property: 'name', type: 'string', example: 'Juan Pérez'),
//                new OA\Property(property: 'email', type: 'string', example: 'juan@example.com'),
//            ]
//        ),
//        new OA\Property(
//            property: 'empleado',
//            type: 'object',
//            properties: [
//                new OA\Property(property: 'id_empleado', type: 'integer', example: 1),
//                new OA\Property(property: 'nombre', type: 'string', example: 'Juan'),
//                new OA\Property(property: 'apellido', type: 'string', example: 'Pérez'),
//                new OA\Property(property: 'email', type: 'string', example: 'juan@example.com'),
//                new OA\Property(property: 'dni', type: 'string', example: '12345678'),
//            ]
//        ),
//        new OA\Property(
//            property: 'rol',
//            type: 'string',
//            example: 'administrador',
//            description: 'Nombre del rol asignado'
//        ),
//        new OA\Property(
//            property: 'permisos',
//            type: 'array',
//            items: new OA\Items(type: 'string'),
//            example: ['ver-empleados', 'crear-blogs', 'editar-productos'],
//            description: 'Lista de permisos del rol'
//        ),
//        new OA\Property(
//            property: 'token',
//            type: 'string',
//            example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
//            description: 'Token de autenticación Bearer'
//        ),
//    ]
//)]
//class AuthSuccessResponse {}
//
//#[OA\Schema(
//    schema: 'LoginSuccessResponse',
//    type: 'object',
//    properties: [
//        new OA\Property(
//            property: 'status',
//            type: 'string',
//            example: 'success'
//        ),
//        new OA\Property(
//            property: 'user',
//            type: 'object',
//            properties: [
//                new OA\Property(property: 'id', type: 'integer'),
//                new OA\Property(property: 'name', type: 'string'),
//                new OA\Property(property: 'email', type: 'string'),
//            ]
//        ),
//        new OA\Property(
//            property: 'empleado',
//            type: 'object'
//        ),
//        new OA\Property(
//            property: 'rol',
//            type: 'string',
//            example: 'empleado'
//        ),
//        new OA\Property(
//            property: 'permisos',
//            type: 'array',
//            items: new OA\Items(type: 'string')
//        ),
//        new OA\Property(
//            property: 'token',
//            type: 'string'
//        ),
//    ]
//)]
//class LoginSuccessResponse {}

//#[OA\Schema(
//    schema: 'ChangePasswordSuccessResponse',
//    type: 'object',
//    properties: [
//        new OA\Property(
//            property: 'message',
//            type: 'string',
//            example: 'Contraseña cambiada exitosamente',
//            description: 'Mensaje de confirmación'
//        ),
//    ]
//)]
//class ChangePasswordSuccessResponse {}

//#[OA\Schema(
//    schema: 'AuthErrorResponse',
//    type: 'object',
//    properties: [
//        new OA\Property(
//            property: 'status',
//            type: 'string',
//            example: 'error'
//        ),
//        new OA\Property(
//            property: 'message',
//            type: 'string',
//            example: 'Error en la operación'
//        ),
//        new OA\Property(
//            property: 'error',
//            type: 'string',
//            description: 'Detalles del error (solo en desarrollo)'
//        ),
//    ]
//)]
//class AuthErrorResponse {}
//
//#[OA\Schema(
//    schema: 'ValidationErrorResponse',
//    type: 'object',
//    properties: [
//        new OA\Property(
//            property: 'status',
//            type: 'string',
//            example: 'error'
//        ),
//        new OA\Property(
//            property: 'message',
//            type: 'string',
//            example: 'Datos inválidos o faltantes'
//        ),
//        new OA\Property(
//            property: 'errors',
//            type: 'object',
//            example: [
//                'email' => ['El email ya está registrado'],
//                'dni' => ['El DNI ya existe'],
//            ]
//        ),
//    ]
//)]
//class ValidationErrorResponse {}
//
//#[OA\Schema(
//    schema: 'UserResponse',
//    type: 'object',
//    properties: [
//        new OA\Property(
//            property: 'user',
//            type: 'object',
//            properties: [
//                new OA\Property(property: 'id', type: 'integer'),
//                new OA\Property(property: 'name', type: 'string'),
//                new OA\Property(property: 'email', type: 'string'),
//            ]
//        ),
//        new OA\Property(
//            property: 'empleado',
//            type: 'object'
//        ),
//        new OA\Property(
//            property: 'rol',
//            type: 'object',
//            properties: [
//                new OA\Property(property: 'id_rol', type: 'integer'),
//                new OA\Property(property: 'nombre', type: 'string'),
//            ]
//        ),
//        new OA\Property(
//            property: 'abilities',
//            type: 'array',
//            items: new OA\Items(type: 'string'),
//            description: 'Capacidades del token actual'
//        ),
//        new OA\Property(
//            property: 'permisos',
//            type: 'array',
//            items: new OA\Items(type: 'string'),
//            description: 'Permisos del rol'
//        ),
//    ]
//)]
//class UserResponse {}
