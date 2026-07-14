<?php

namespace App\Http\Requests\Testimonio;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTestimonioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'  => 'required|string|max:150',
            'texto'   => 'required|string|max:600',
            'rating'  => 'required|integer|min:1|max:5',
            'fecha'   => 'nullable|date',
            'avatar'  => 'nullable|image|max:2048',
            'activo'  => 'nullable|boolean',
            'orden'   => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'texto.required'  => 'El texto del testimonio es obligatorio.',
            'texto.max'       => 'El texto no puede superar los 600 caracteres.',
            'rating.required' => 'La calificación es obligatoria.',
            'rating.integer'  => 'La calificación debe ser un número entero.',
            'rating.min'      => 'La calificación mínima es 1.',
            'rating.max'      => 'La calificación máxima es 5.',
            'fecha.date'      => 'La fecha no es válida.',
            'avatar.image'    => 'El avatar debe ser una imagen.',
            'avatar.max'      => 'El avatar no puede superar los 2MB.',
            'activo.boolean'  => 'El estado activo debe ser verdadero o falso.',
            'orden.integer'   => 'El orden debe ser un número entero.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
}