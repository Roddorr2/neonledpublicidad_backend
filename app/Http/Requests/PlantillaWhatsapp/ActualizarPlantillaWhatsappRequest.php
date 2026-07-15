<?php

namespace App\Http\Requests\PlantillaWhatsapp;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizarPlantillaWhatsappRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mensaje' => ['required', 'string', 'max:5000'],
            'imagen'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'mensaje.required' => 'El mensaje de la plantilla es obligatorio.',
            'mensaje.string'   => 'El mensaje debe ser texto.',
            'mensaje.max'      => 'El mensaje no puede superar los 5000 caracteres.',
            'imagen.image'     => 'El archivo debe ser una imagen.',
            'imagen.mimes'     => 'La imagen debe ser de tipo: jpg, jpeg, png o webp.',
            'imagen.max'       => 'La imagen no puede superar los 5 MB (5120 KB).',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
}
