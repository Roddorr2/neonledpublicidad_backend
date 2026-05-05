<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogFooterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
            'public_image1' => 'nullable|string',
            'url_image1' => 'nullable|string',
            'public_image2' => 'nullable|string',
            'url_image2' => 'nullable|string',
            'public_image3' => 'nullable|string',
            'url_image3' => 'nullable|string',
            'alt_image1' => 'nullable|string|min:60|max:120',
            'title_image1' => 'nullable|string|min:50|max:70',
            'alt_image2' => 'nullable|string|min:60|max:120',
            'title_image2' => 'nullable|string|min:50|max:70',
            'alt_image3' => 'nullable|string|min:60|max:120',
            'title_image3' => 'nullable|string|min:50|max:70',
            'estado' => 'nullable|boolean',
            'keyword' => 'nullable|string',
            'link' => 'nullable|string'
        ];
    }
}