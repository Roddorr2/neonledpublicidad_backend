<?php

namespace App\Http\Requests\Rol;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SyncPermisosRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permisos'   => ['required', 'array'],
            'permisos.*' => ['exists:permisos,id_permiso'],
        ];
    }

    public function messages(): array
    {
        return [
            'permisos.required'  => 'Debes enviar al menos un permiso.',
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