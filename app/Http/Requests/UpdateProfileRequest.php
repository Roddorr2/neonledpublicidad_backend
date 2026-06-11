<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $user    = $this->user();
        $cliente = \App\Models\Cliente::where('id_user', $user->id)->first();

        return [
            'nombre'   => 'required|string|max:191',
            'apellido' => 'required|string|max:191',
            'telefono' => 'required|string|max:14',
            'distrito' => 'nullable|string|max:191',

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
                Rule::unique('clientes', 'email')->ignore($cliente?->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // nombre
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string'   => 'El nombre debe ser una cadena de texto.',
            'nombre.max'      => 'El nombre no debe superar los 191 caracteres.',

            // apellido
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.string'   => 'El apellido debe ser una cadena de texto.',
            'apellido.max'      => 'El apellido no debe superar los 191 caracteres.',

            // telefono
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.string'   => 'El teléfono debe ser una cadena de texto.',
            'telefono.max'      => 'El teléfono no debe superar los 14 caracteres.',

            // distrito
            'distrito.string' => 'El distrito debe ser una cadena de texto.',
            'distrito.max'    => 'El distrito no debe superar los 191 caracteres.',

            // email
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'El correo electrónico debe tener un formato válido.',
            'email.unique'   => 'El correo electrónico ya está en uso.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors'  => $validator->errors(),
        ], 422));
    }
}
