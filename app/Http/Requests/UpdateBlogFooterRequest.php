<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogFooterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'titulo'        => 'required|string',
            'descripcion'   => 'required|string',
            'public_image1' => 'nullable|string',
            'url_image1'    => 'nullable|string',
            'public_image2' => 'nullable|string',
            'url_image2'    => 'nullable|string',
            'public_image3' => 'nullable|string',
            'url_image3' => 'nullable|string',
            'alt_image1' => 'nullable|string',
            'title_image1' => 'nullable|string',
            'alt_image2' => 'nullable|string',
            'title_image2' => 'nullable|string',
            'alt_image3' => 'nullable|string',
            'title_image3' => 'nullable|string',
            'estado' => 'nullable|boolean',
            'keyword' => 'nullable|string',
            'link' => 'nullable|string'
        ];
    }
}
