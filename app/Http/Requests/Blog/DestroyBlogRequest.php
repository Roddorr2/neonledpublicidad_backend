<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DestroyBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_empleado' => 'required|integer|exists:empleados,id_empleado',
        ];
    }

    public function messages(): array
    {
        return [
            'id_empleado.required' => 'El empleado es obligatorio.',
            'id_empleado.exists'   => 'El empleado no existe.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 400)
        );
    }
}