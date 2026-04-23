<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpleadoRequest extends FormRequest
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
        $empleado = $this->route('id')
            ? \App\Models\Empleado::where('id_empleado', $this->route('id'))->first()
            : null;

        return [
            'nombre'   => 'sometimes|string|max:255',
            'apellido' => 'sometimes|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'id_rol'   => 'sometimes|exists:roles,id_rol',

            'dni' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('empleados', 'dni')->ignore($this->route('id'), 'id_empleado'),
            ],

            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('empleados', 'email')->ignore($this->route('id'), 'id_empleado'),
                $empleado
                    ? Rule::unique('users', 'email')->ignore($empleado->id_user)
                    : null,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar los 255 caracteres.',

            'apellido.string' => 'El apellido debe ser texto.',
            'apellido.max' => 'El apellido no debe superar los 255 caracteres.',

            'email.email' => 'El correo electrónico no es válido.',
            'email.max' => 'El correo electrónico no debe superar los 255 caracteres.',
            'email.unique' => 'El correo electrónico ya está en uso.',

            'dni.max' => 'El DNI no debe superar los 20 caracteres.',
            'dni.unique' => 'El DNI ya está registrado.',

            'telefono.string' => 'El teléfono debe ser texto.',
            'telefono.max' => 'El teléfono no debe superar los 20 caracteres.',

            'id_rol.exists' => 'El rol seleccionado no es válido.',
        ];
    }

    protected function failedValidation($validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
            'status' => 422,
            'errors' => $validator->errors()
        ], 422));
    }
}


