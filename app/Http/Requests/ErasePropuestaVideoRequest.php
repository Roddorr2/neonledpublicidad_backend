<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ErasePropuestaVideoRequest extends FormRequest
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
            'id_cliente' => 'required|numeric|exists:clientes,id',
            'filename' => 'required|string',
            'extension' => 'required|string'
        ];
    }

    public function messages(): array
    {
        return [
            'id_cliente.required' => 'El ID del cliente es obligatorio',
            'id_cliente.numeric' => 'El ID del cliente debe ser un número',
            'id_cliente.exists' => 'El cliente no existe',

            'filename.required' => 'El nombre del archivo es obligatorio',
            'filename.string' => 'El nombre del archivo debe ser una cadena de texto',

            'extension.required' => 'La extensión del archivo es obligatoria',
            'extension.string' => 'La extensión debe ser una cadena de texto'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422));
    }
}