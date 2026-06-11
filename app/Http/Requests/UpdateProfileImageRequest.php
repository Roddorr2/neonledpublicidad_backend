<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileImageRequest extends FormRequest
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
            'imagen'     => 'nullable|image|max:2048',
            'public_id'  => 'required_without:imagen|string',
            'secure_url' => 'required_without:imagen|url',
        ];
    }

    public function messages(): array
    {
        return [
            //
            // imagen
            'imagen.image' => 'El archivo debe ser una imagen válida.',
            'imagen.max'   => 'La imagen no debe superar los 2MB.',

            // public_id
            'public_id.required_without' => 'El campo public_id es obligatorio cuando no se envía una imagen.',
            'public_id.string'           => 'El public_id debe ser una cadena de texto.',

            // secure_url
            'secure_url.required_without' => 'El campo secure_url es obligatorio cuando no se envía una imagen.',
            'secure_url.url'              => 'El campo secure_url debe ser una URL válida.',
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
