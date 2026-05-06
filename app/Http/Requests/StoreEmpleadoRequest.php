<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEmpleadoRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:empleados,email|unique:users,email',
            'dni' => 'required|string|max:20|unique:empleados,dni',
            'telefono' => 'nullable|string|max:20',
            'id_rol' => 'required|exists:roles,id_rol',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar los 255 caracteres.',

            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.string' => 'El apellido debe ser texto.',
            'apellido.max' => 'El apellido no debe superar los 255 caracteres.',

            'email.required' => 'El correo electrónico es obligatorio.',
            'email.string' => 'El correo electrónico debe ser texto.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.max' => 'El correo electrónico no debe superar los 255 caracteres.',
            'email.unique' => 'El correo electrónico ya está en uso.',

            'dni.required' => 'El DNI es obligatorio.',
            'dni.string' => 'El DNI debe ser texto.',
            'dni.max' => 'El DNI no debe superar los 20 caracteres.',
            'dni.unique' => 'El DNI ya está registrado.',

            'telefono.string' => 'El teléfono debe ser texto.',
            'telefono.max' => 'El teléfono no debe superar los 20 caracteres.',

            'id_rol.required' => 'El rol es obligatorio.',
            'id_rol.exists' => 'El rol seleccionado no es válido.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            //'message' => 'Errores de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}

