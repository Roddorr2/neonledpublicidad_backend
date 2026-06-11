<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCardRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'titulo'             => 'required|string|max:255',
            'descripcion'        => 'required|string',
            'public_image'       => 'required|string',
            'url_image'          => 'nullable|string',
            'id_plantilla'       => 'required|integer|min:1|max:3',
            'id_blog'            => 'required|integer|exists:blogs,id_blog',
            'id_empleado'        => 'required|integer|exists:empleados,id_empleado',
            'estado_publicacion' => 'nullable|boolean',
        ];
    }
}
