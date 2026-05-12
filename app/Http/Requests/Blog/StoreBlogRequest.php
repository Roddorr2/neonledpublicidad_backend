<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_blog_head'    => 'required|integer|exists:blog_heads,id_blog_head',
            'id_blog_body'    => 'required|integer|exists:blog_bodies,id_blog_body',
            'id_blog_footer'  => 'required|integer|exists:blog_footers,id_blog_footer',
            'fecha'           => 'required|date',
            'id_empleado'     => 'required|integer|exists:empleados,id_empleado',
            'link'            => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'id_blog_head.required'   => 'El encabezado del blog es obligatorio.',
            'id_blog_head.exists'     => 'El encabezado del blog no existe.',
            'id_blog_body.required'   => 'El cuerpo del blog es obligatorio.',
            'id_blog_body.exists'     => 'El cuerpo del blog no existe.',
            'id_blog_footer.required' => 'El pie del blog es obligatorio.',
            'id_blog_footer.exists'   => 'El pie del blog no existe.',
            'fecha.required'          => 'La fecha es obligatoria.',
            'fecha.date'              => 'La fecha no tiene un formato válido.',
            'id_empleado.required'    => 'El empleado es obligatorio.',
            'id_empleado.exists'      => 'El empleado no existe.',
            'link.max'                => 'El link no puede superar los 255 caracteres.',
        ];
    }

    // Devuelve los errores en formato JSON igual que antes
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 400)
        );
    }
}