<?php

namespace App\Http\Requests\Modal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreModalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'      => 'required|string|max:100',
            'telefono'    => 'required|string|max:9',
            'correo'      => 'required|email|max:200',
            'id_producto' => 'required|integer|min:1|max:16',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.string'        => 'El nombre debe ser texto.',
            'nombre.max'           => 'El nombre no puede superar los 100 caracteres.',

            'telefono.required'    => 'El teléfono es obligatorio.',
            'telefono.string'      => 'El teléfono debe ser texto.',
            'telefono.max'         => 'El teléfono no puede superar los 9 caracteres.',

            'correo.required'      => 'El correo es obligatorio.',
            'correo.email'         => 'El correo no tiene un formato válido.',
            'correo.max'           => 'El correo no puede superar los 200 caracteres.',

            'id_producto.required' => 'El producto es obligatorio.',
            'id_producto.integer'  => 'El id del producto debe ser un número entero.',
            'id_producto.min'      => 'El id del producto debe ser al menos 1.',
            'id_producto.max'      => 'El id del producto no puede superar el valor 15.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'error'   => 'Error en la validación',
                'details' => $validator->errors()
            ], 400)
        );
    }
}