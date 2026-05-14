<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
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
        $clienteId = $this->route('id'); // obtiene el parámetro de la URL

        return [
            'id' => ['required', 'numeric', 'exists:clientes,id'],

            'nombre'   => ['sometimes', 'string', 'max:255'],
            'apellido' => ['sometimes', 'string', 'max:255'],

            'email' => [
                'sometimes',
                'email',
                /*
                    SELECT * FROM clientes
                    WHERE email = 'correo@ejemplo.com'
                    AND id != $clienteId
                */
                Rule::unique('clientes', 'email')->ignore($clienteId),
                Rule::unique('users', 'email')->ignore($this->user_id_from_cliente()),
            ],

            'telefono' => ['nullable', 'string', 'max:14'],
            'distrito' => ['nullable', 'string', 'max:191'],
        ];
    }

    /**
     * Obtener el user_id relacionado al cliente (para ignorar unique correctamente)
     */
    private function user_id_from_cliente()
    {
        $cliente = Cliente::find($this->route('id'));

        return $cliente?->id_user;
    }

    public function messages(): array
    {
        return [
            'id.required' => 'El id es obligatorio',
            'id.numeric'  => 'El id debe ser numérico',
            'id.exists'   => 'El cliente no existe',

            'email.email'  => 'El correo es inválido',
            'email.unique' => 'El correo ya está en uso',

            'nombre.string'   => 'El nombre no es válido',
            'apellido.string' => 'El apellido no es válido',
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
