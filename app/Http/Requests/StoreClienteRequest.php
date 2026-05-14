<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreClienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'nombre'   => ['required', 'string', 'max:191'],
            'apellido' => ['required', 'string', 'max:191'],
            'email'    => ['required', 'email', 'unique:users,email', 'unique:clientes,email'],
            'telefono' => ['required', 'string', 'max:14'],
            'distrito' => ['nullable', 'string', 'max:191'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.string'   => 'El nombre no tiene formato correcto',
            'nombre.max'      => 'El nombre no puede tener mas de 191 caracteres',

            'apellido.required' => 'El apellido es obligatorio',
            'apellido.string'   => 'El apellido no tiene formato correcto',
            'apellido.max'      => 'El apellido no puede tener mas de 191 caracteres',

            'email.required' => 'El correo es obligatorio',
            'email.email'    => 'El correo es inválido',
            'email.unique'   => 'El correo electrónico ya está en uso por otro cliente, por favor ingrese otro correo',

            'telefono.required' => 'El telefono es obligatorio',
            'telefono.string'   => 'El telefono no tiene formato correcto',
            'telefono.max'      => 'El telefono no puede tener mas de 14 caracteres',

            'distrito.string' => 'El distrito no tiene formato correcto',
            'distrito.max'    => 'El distrito no puede tener mas de 191 caracteres',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            // 'message' => 'Errores de validación',
            'errors' => $validator->errors(),
        ], 422));
    }
}
