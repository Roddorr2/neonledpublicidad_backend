<?php

namespace App\Http\Requests\Blog;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'id_blog_head'   => [
                'required',
                'integer',
                'exists:blog_heads,id_blog_head',
                Rule::unique('blogs', 'id_blog_head')->ignore($id, 'id_blog'),
            ],
            'id_blog_body'   => [
                'required',
                'integer',
                'exists:blog_bodies,id_blog_body',
                Rule::unique('blogs', 'id_blog_body')->ignore($id, 'id_blog'),
            ],
            'id_blog_footer' => [
                'required',
                'integer',
                'exists:blog_footers,id_blog_footer',
                Rule::unique('blogs', 'id_blog_footer')->ignore($id, 'id_blog'),
            ],
            'fecha'          => 'required|date',
            'id_empleado'    => 'required|integer|exists:empleados,id_empleado',
            'descripcion'    => 'nullable|string',
            'link'           => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'id_blog_head.required'   => 'El encabezado del blog es obligatorio.',
            'id_blog_head.exists'     => 'El encabezado del blog no existe.',
            'id_blog_head.unique'     => 'El encabezado ya está asociado a otro blog.',
            'id_blog_body.required'   => 'El cuerpo del blog es obligatorio.',
            'id_blog_body.exists'     => 'El cuerpo del blog no existe.',
            'id_blog_body.unique'     => 'El cuerpo ya está asociado a otro blog.',
            'id_blog_footer.required' => 'El pie del blog es obligatorio.',
            'id_blog_footer.exists'   => 'El pie del blog no existe.',
            'id_blog_footer.unique'   => 'El pie del blog ya está asociado a otro blog.',
            'fecha.required'          => 'La fecha es obligatoria.',
            'fecha.date'              => 'La fecha no tiene un formato válido.',
            'id_empleado.required'    => 'El empleado es obligatorio.',
            'id_empleado.exists'      => 'El empleado no existe.',
            'link.max'                => 'El link no puede superar los 255 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 400)
        );
    }
}
