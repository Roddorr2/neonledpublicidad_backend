<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmpleadoProfileImageRequest extends FormRequest
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
            // modo archivo
            'imagen' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',

            // modo manual
            'public_id' => 'required_without:imagen|string',
            'secure_url' => 'required_without:imagen|url',
        ];
    }

    public function messages(): array
    {
        return [
            'imagen.file' => 'El archivo debe ser válido.',
            'imagen.mimes' => 'La imagen debe ser JPG, JPEG, PNG o WEBP.',
            'imagen.max' => 'La imagen no debe superar los 2 MB.',

            'public_id.required_without' => 'El public_id es obligatorio si no se envía una imagen.',
            'public_id.string' => 'El public_id debe ser texto.',

            'secure_url.required_without' => 'La URL es obligatoria si no se envía una imagen.',
            'secure_url.url' => 'La URL de la imagen no es válida.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 422,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}
