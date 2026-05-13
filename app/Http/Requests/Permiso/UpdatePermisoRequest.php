<?php

namespace App\Http\Requests\Permiso;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePermisoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('permiso');

        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permisos', 'nombre')->ignore($id, 'id_permiso'),
            ],
            'descripcion' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del permiso es obligatorio.',
            'nombre.string'   => 'El nombre debe ser una cadena de texto.',
            'nombre.max'      => 'El nombre no puede superar los 255 caracteres.',
            'nombre.unique'   => 'Ya existe otro permiso con ese nombre.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
}