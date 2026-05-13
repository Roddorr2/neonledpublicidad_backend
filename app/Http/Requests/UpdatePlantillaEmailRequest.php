<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlantillaEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'nullable|string|max:255',
            'asunto' => 'required|string|max:255',
            'encabezado' => 'required|string|max:255',
            'mensaje' => 'required|string|max:10000',
            'mensaje_boton' => 'nullable|string|max:255',
            'url_boton' => 'nullable|url|max:2048',
            'footer' => 'nullable|string|max:10000',
            'red_facebook' => 'nullable|url|max:2048',
            'red_tiktok' => 'nullable|url|max:2048',
            'red_instagram' => 'nullable|url|max:2048',
            'red_linkedin' => 'nullable|url|max:2048',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }
}