<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommendTarjetaRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'titulo' => 'nullable|string|max:255',
            'texto1' => 'nullable|string|max:255',
            'texto2' => 'nullable|string|max:255',
            'texto3' => 'nullable|string|max:255',
            'texto4' => 'nullable|string|max:255',
            'texto5' => 'nullable|string|max:255',
        ];
    }
}