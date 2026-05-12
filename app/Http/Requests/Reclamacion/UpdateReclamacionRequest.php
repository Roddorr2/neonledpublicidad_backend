<?php

namespace App\Http\Requests\Reclamacion;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateReclamacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estadoReclamo' => ['required', 'string', 'in:PENDIENTE,ATENDIDO'],
        ];
    }

    public function messages(): array
    {
        return [
            'estadoReclamo.required' => 'El estado del reclamo es obligatorio.',
            'estadoReclamo.in'       => 'El estado debe ser PENDIENTE o ATENDIDO.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
}