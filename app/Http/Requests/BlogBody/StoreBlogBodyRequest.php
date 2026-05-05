<?php

namespace App\Http\Requests\BlogBody;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBlogBodyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo'               => 'required|string',
            'descripcion'          => 'required|string',
            'id_commend_tarjeta'   => 'nullable|integer|exists:commend_tarjetas,id_commend_tarjeta',
            'public_image1'        => 'nullable|string',
            'url_image1'           => 'nullable|string',
            'alt_image1'           => 'nullable|string|min:60|max:120',
            'title_image1'         => 'nullable|string|min:50|max:70',
            'public_image2'        => 'nullable|string',
            'url_image2'           => 'nullable|string',
            'alt_image2'           => 'nullable|string|min:60|max:120',
            'title_image2'         => 'nullable|string|min:50|max:70',
            'public_image3'        => 'nullable|string',
            'url_image3'           => 'nullable|string',
            'alt_image3'           => 'nullable|string|min:60|max:120',
            'title_image3'         => 'nullable|string|min:50|max:70',
            'flag_galeria'         => 'nullable|boolean',
            'flag_consejos'        => 'nullable|boolean',
            'flag_informacion'     => 'nullable|boolean',
            'titulo_tarjeta'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required'         => 'El título es obligatorio.',
            'descripcion.required'    => 'La descripción es obligatoria.',
            'alt_image1.min'          => 'El alt de la imagen 1 debe tener al menos 60 caracteres.',
            'alt_image1.max'          => 'El alt de la imagen 1 no puede superar los 120 caracteres.',
            'title_image1.min'        => 'El title de la imagen 1 debe tener al menos 50 caracteres.',
            'title_image1.max'        => 'El title de la imagen 1 no puede superar los 70 caracteres.',
            'alt_image2.min'          => 'El alt de la imagen 2 debe tener al menos 60 caracteres.',
            'alt_image2.max'          => 'El alt de la imagen 2 no puede superar los 120 caracteres.',
            'title_image2.min'        => 'El title de la imagen 2 debe tener al menos 50 caracteres.',
            'title_image2.max'        => 'El title de la imagen 2 no puede superar los 70 caracteres.',
            'alt_image3.min'          => 'El alt de la imagen 3 debe tener al menos 60 caracteres.',
            'alt_image3.max'          => 'El alt de la imagen 3 no puede superar los 120 caracteres.',
            'title_image3.min'        => 'El title de la imagen 3 debe tener al menos 50 caracteres.',
            'title_image3.max'        => 'El title de la imagen 3 no puede superar los 70 caracteres.',
            'id_commend_tarjeta.exists' => 'La commend tarjeta no existe.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 400)
        );
    }
}