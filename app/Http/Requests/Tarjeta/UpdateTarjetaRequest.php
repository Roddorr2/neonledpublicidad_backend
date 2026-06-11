<?php

namespace App\Http\Requests\Tarjeta;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTarjetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo'       => ['required', 'string', 'max:70'],
            'descripcion'  => ['required', 'string'],
            'keyword'      => ['nullable', 'string'],
            'link'         => ['nullable', 'string'],
            'id_blog_body' => ['required', 'integer', 'exists:blog_bodies,id_blog_body'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required'       => 'El título es obligatorio.',
            'titulo.string'         => 'El título debe ser texto.',
            'titulo.max'            => 'El título no puede superar los 70 caracteres.',
            'descripcion.required'  => 'La descripción es obligatoria.',
            'descripcion.string'    => 'La descripción debe ser texto.',
            'id_blog_body.required' => 'El cuerpo del blog es obligatorio.',
            'id_blog_body.integer'  => 'El cuerpo del blog debe ser un número entero.',
            'id_blog_body.exists'   => 'El cuerpo del blog no existe.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
}