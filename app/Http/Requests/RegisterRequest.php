<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
            'nombre'   => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:empleados,email|unique:users,email',
            'dni'      => 'required|string|max:20|unique:empleados,dni',
            'telefono' => 'nullable|string|max:20',

        ];
    }

    public function messages(): array
    {
        return [
            // nombre
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.string'   => 'El nombre debe ser una cadena de texto.',
            'nombre.max'      => 'El nombre no debe superar los 255 caracteres.',

            // apellido
            'apellido.required' => 'El campo apellido es obligatorio.',
            'apellido.string'   => 'El apellido debe ser una cadena de texto.',
            'apellido.max'      => 'El apellido no debe superar los 255 caracteres.',

            // email
            'email.required' => 'El campo correo electrónico es obligatorio.',
            'email.string'   => 'El correo electrónico debe ser una cadena de texto.',
            'email.email'    => 'El correo electrónico no tiene un formato válido.',
            'email.max'      => 'El correo electrónico no debe superar los 255 caracteres.',
            'email.unique'   => 'El correo electrónico ya está registrado.',

            // dni
            'dni.required' => 'El campo DNI es obligatorio.',
            'dni.string'   => 'El DNI debe ser una cadena de texto.',
            'dni.max'      => 'El DNI no debe superar los 20 caracteres.',
            'dni.unique'   => 'El DNI ya está registrado.',

            // telefono
            'telefono.string' => 'El teléfono debe ser una cadena de texto.',
            'telefono.max'    => 'El teléfono no debe superar los 20 caracteres.',

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
