<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePropuestaRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:20480|mimetypes:image/jpeg,image/jpg,image/png,image/gif,image/webp,image/avif,image/pjpeg,image/jfif',
            'videos' => 'nullable|array|max:5',
            'videos.*' => 'file|max:51200|mimetypes:video/mp4,video/webm,video/ogg,application/octet-stream,video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi,video/qt'
        ];
    }

    public function messages(): array
    {
        return [
            'id_cliente.required' => 'El ID del cliente es obligatorio',
            'id_cliente.numeric' => 'El ID del cliente debe ser un número',
            'id_cliente.exists' => 'El cliente no existe',

            'nombre.required' => 'El nombre es obligatorio',
            'nombre.string' => 'El nombre debe ser una cadena de texto',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres',

            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.string' => 'La descripción debe ser una cadena de texto',

            'files.array' => 'Los archivos deben ser un arreglo',
            'files.max' => 'No puedes subir más de 10 imágenes',
            'files.*.file' => 'Cada archivo debe ser un archivo válido',
            'files.*.max' => 'Cada imagen no puede ser mayor a 20MB',
            'files.*.mimetypes' => 'Las imágenes deben ser de tipo: jpeg, jpg, png, gif, webp, avif, pjpeg, jfif',

            'videos.array' => 'Los videos deben ser un arreglo',
            'videos.max' => 'No puedes subir más de 5 videos',
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