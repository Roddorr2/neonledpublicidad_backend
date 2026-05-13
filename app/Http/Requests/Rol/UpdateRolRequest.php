<?php

namespace App\Http\Requests\Rol;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('rol');

        return [
            'nombre'     => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'nombre')->ignore($id, 'id_rol'),
            ],
            'permisos'   => ['nullable', 'array'],
            'permisos.*' => ['exists:permisos,id_permiso'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'    => 'El nombre del rol es obligatorio.',
            'nombre.string'      => 'El nombre debe ser una cadena de texto.',
            'nombre.max'         => 'El nombre no puede superar los 255 caracteres.',
            'nombre.unique'      => 'Ya existe otro rol con ese nombre.',
            'permisos.array'     => 'Los permisos deben ser un arreglo.',
            'permisos.*.exists'  => 'Uno o más permisos seleccionados no existen.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
}