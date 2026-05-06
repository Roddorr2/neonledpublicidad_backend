<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'                    => 'required|string|max:250',
            'descripcion'               => 'required|string',
            'id_empleado'               => 'nullable|integer|exists:empleados,id_empleado',

            'file_main'                 => 'nullable|image|max:2048',
            'file_background'           => 'nullable|image|max:2048',

            'path1'                     => 'nullable|string|max:255',
            'tituloimg1'                => 'nullable|string|max:255',
            'descripcionimg1'           => 'nullable|string',

            'path2'                     => 'nullable|string|max:255',
            'tituloimg2'                => 'nullable|string|max:255',
            'descripcionimg2'           => 'nullable|string',

            'path3'                     => 'nullable|string|max:255',
            'tituloimg3'                => 'nullable|string|max:255',
            'descripcionimg3'           => 'nullable|string',

            'caracteristicas_descrip'   => 'nullable|string',
            'ventajas_descrip'          => 'nullable|string',
            'consumoenergetico_descrip' => 'nullable|string',
            'iluminacion_descrip'       => 'nullable|string',
            'durabilidad_descrip'       => 'nullable|string',
            'estado'                    => 'boolean',

            'file1'                     => 'nullable|file|max:2048|mimes:jpeg,jpg,png,gif,webp,avif',
            'file2'                     => 'nullable|file|max:2048|mimes:jpeg,jpg,png,gif,webp,avif',
            'file3'                     => 'nullable|file|max:2048|mimes:jpeg,jpg,png,gif,webp,avif',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'          => 'El nombre del producto es obligatorio.',
            'nombre.max'               => 'El nombre no puede superar los 250 caracteres.',
            'descripcion.required'     => 'La descripción del producto es obligatoria.',
            'id_empleado.integer'      => 'El id del empleado debe ser un número entero.',
            'id_empleado.exists'       => 'El empleado no existe.',

            'file_main.image'          => 'El archivo principal debe ser una imagen.',
            'file_main.max'            => 'La imagen principal no puede superar los 2MB.',
            'file_background.image'    => 'El archivo de fondo debe ser una imagen.',
            'file_background.max'      => 'La imagen de fondo no puede superar los 2MB.',

            'path1.max'                => 'El path de la imagen 1 no puede superar los 255 caracteres.',
            'path2.max'                => 'El path de la imagen 2 no puede superar los 255 caracteres.',
            'path3.max'                => 'El path de la imagen 3 no puede superar los 255 caracteres.',

            'tituloimg1.max'           => 'El título de la imagen 1 no puede superar los 255 caracteres.',
            'tituloimg2.max'           => 'El título de la imagen 2 no puede superar los 255 caracteres.',
            'tituloimg3.max'           => 'El título de la imagen 3 no puede superar los 255 caracteres.',

            'file1.max'                => 'La imagen 1 no puede superar los 2MB.',
            'file1.mimes'              => 'La imagen 1 debe ser de tipo: jpeg, jpg, png, gif, webp o avif.',
            'file2.max'                => 'La imagen 2 no puede superar los 2MB.',
            'file2.mimes'              => 'La imagen 2 debe ser de tipo: jpeg, jpg, png, gif, webp o avif.',
            'file3.max'                => 'La imagen 3 no puede superar los 2MB.',
            'file3.mimes'              => 'La imagen 3 debe ser de tipo: jpeg, jpg, png, gif, webp o avif.',

            'estado.boolean'           => 'El estado debe ser verdadero o falso.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 400)
        );
    }
}