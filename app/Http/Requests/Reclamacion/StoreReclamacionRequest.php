<?php

namespace App\Http\Requests\Reclamacion;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreReclamacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'                   => ['required', 'string', 'max:100'],
            'apellido'                 => ['required', 'string', 'max:100'],
            'email'                    => ['required', 'email', 'max:100'],
            'telefono'                 => ['required', 'string', 'max:20'],
            'departamento'             => ['nullable', 'string', 'max:100'],
            'direccion'                => ['required', 'string', 'max:250'],
            'distrito'                 => ['required', 'string', 'max:250'],
            'id_servicio'              => ['required', 'integer', 'exists:servicios,id_servicio'],
            'fechaIncidente'           => ['required', 'date'],
            'montoReclamado'           => ['nullable', 'numeric'],
            'descripcionServicio'      => ['required', 'string', 'max:1050'],
            'checkReclamoForm'         => ['required', 'boolean'],
            'aceptaPoliticaPrivacidad' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'                   => 'El nombre es obligatorio.',
            'nombre.max'                        => 'El nombre no puede superar los 100 caracteres.',
            'apellido.required'                 => 'El apellido es obligatorio.',
            'apellido.max'                      => 'El apellido no puede superar los 100 caracteres.',
            'email.required'                    => 'El email es obligatorio.',
            'email.email'                       => 'El email no tiene un formato válido.',
            'email.max'                         => 'El email no puede superar los 100 caracteres.',
            'telefono.required'                 => 'El teléfono es obligatorio.',
            'telefono.max'                      => 'El teléfono no puede superar los 20 caracteres.',
            'direccion.required'                => 'La dirección es obligatoria.',
            'direccion.max'                     => 'La dirección no puede superar los 250 caracteres.',
            'distrito.required'                 => 'El distrito es obligatorio.',
            'distrito.max'                      => 'El distrito no puede superar los 250 caracteres.',
            'id_servicio.required'              => 'El servicio es obligatorio.',
            'id_servicio.integer'               => 'El servicio debe ser un número entero.',
            'id_servicio.exists'                => 'El servicio seleccionado no existe.',
            'fechaIncidente.required'           => 'La fecha del incidente es obligatoria.',
            'fechaIncidente.date'               => 'La fecha del incidente no tiene un formato válido.',
            'montoReclamado.numeric'            => 'El monto reclamado debe ser un número.',
            'descripcionServicio.required'      => 'La descripción del servicio es obligatoria.',
            'descripcionServicio.max'           => 'La descripción no puede superar los 1050 caracteres.',
            'checkReclamoForm.required'         => 'Debes marcar el tipo de reclamo.',
            'checkReclamoForm.boolean'          => 'El campo checkReclamoForm debe ser verdadero o falso.',
            'aceptaPoliticaPrivacidad.required' => 'Debes aceptar la política de privacidad.',
            'aceptaPoliticaPrivacidad.boolean'  => 'El campo aceptaPoliticaPrivacidad debe ser verdadero o falso.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
}