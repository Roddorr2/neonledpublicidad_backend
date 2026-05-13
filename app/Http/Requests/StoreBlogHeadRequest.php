<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogHeadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'titulo' => 'required|string',
            'texto_frase' => 'required|string|max:70',
            'texto_descripcion' => 'required|string|max:120',
            'public_image' => 'nullable|string',
            'url_image' => 'nullable|string',
            'alt' => 'nullable|string|min:60|max:120',
            'title' => 'nullable|string|min:50|max:70',
            'meta_title' => 'nullable|string|min:50|max:60',
            'meta_descripcion' => 'nullable|string|min:150|max:160'
        ];
    }
}