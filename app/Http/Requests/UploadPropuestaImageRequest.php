<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadPropuestaImageRequest extends FormRequest
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
            'files' => 'nullable|array',
            'files.*' => 'file|max:20480|mimetypes:image/jpeg,image/jpg,image/png,image/gif,image/webp,image/avif,image/pjpeg,image/jfif'
        ];
    }

    public function messages(): array
    {
        return [
            'files.array' => 'Los archivos deben ser un arreglo',
            'files.*.file' => 'Cada archivo debe ser un archivo válido',
            'files.*.max' => 'Cada imagen no puede ser mayor a 20MB',
            'files.*.mimetypes' => 'Las imágenes deben ser de tipo: jpeg, jpg, png, gif, webp, avif, pjpeg, jfif'
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