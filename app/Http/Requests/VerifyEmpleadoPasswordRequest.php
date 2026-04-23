<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmpleadoPasswordRequest extends FormRequest
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
            'currentPassword' => 'required|string',
            'id_empleado' => 'required|exists:empleados,id_empleado',
        ];
    }

    public function messages(): array
    {
        return [
            'currentPassword.required' => 'La contraseña actual es obligatoria.',
            'currentPassword.string' => 'La contraseña debe ser texto.',

            'id_empleado.required' => 'El ID del empleado es obligatorio.',
            'id_empleado.exists' => 'El empleado no existe.',
        ];
    }

    protected function failedValidation($validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
            'status' => 422,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}


