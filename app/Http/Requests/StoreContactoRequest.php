<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreContactoRequest extends FormRequest
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
            'nombre' => [
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        $fail('Nombre es obligatorio');
                    }
                    if (preg_match('/[0-9]/', $value)) {
                        $fail('Nombre no puede tener numeros');
                    }
                },
            ],
            'apellido' => [
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        $fail('Apellido es obligatorio');
                    }
                    if (preg_match('/[0-9]/', $value)) {
                        $fail('Apellido no puede tener numeros');
                    }
                },
            ],
            'telefono' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (! validarTelefonoPeruano($value)) {
                        $fail('Formato de telefono invalido');
                    }
                },
            ],
            'distrito' => ['required'],
            'email'    => [
                'required',
                'email',
            ],
            'tipo_reclamo' => ['required', 'in:CONSULTA,RECLAMO,SUGERENCIA'],
            'mensaje'      => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo es obligatorio',
            'email.email'    => 'El correo es inválido',

            'tipo_reclamo.required' => 'El tipo de reclamo es obligatorio',
            'tipo_reclamo.in'       => 'El tipo de reclamo debe ser CONSULTA, RECLAMO o SUGERENCIA',

            'mensaje.required' => 'Mensaje es obligatorio',
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