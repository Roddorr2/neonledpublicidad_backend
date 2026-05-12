<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadPropuestaVideoRequest extends FormRequest
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
            'videos' => 'required|array',
            'videos.*' => 'file|max:51200|mimetypes:video/mp4,video/webm,video/ogg,application/octet-stream,video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi,video/qt'
        ];
    }

    public function messages(): array
    {
        return [
            'videos.required' => 'Los videos son obligatorios',
            'videos.array' => 'Los videos deben ser un arreglo',
            'videos.*.file' => 'Cada video debe ser un archivo válido',
            'videos.*.max' => 'Cada video no puede ser mayor a 50MB',
            'videos.*.mimetypes' => 'Los videos deben ser de tipo: mp4, webm, ogg, avi, mov, etc.'
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