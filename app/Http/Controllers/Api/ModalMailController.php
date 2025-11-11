<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailModal;
use App\Models\modalservicios;
use App\Mail\MailService;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ModalMailController extends Controller
{
    public function sendMail($id)
    {

        $modal_mail = EmailModal::find($id);

        if (!$modal_mail) {
            return response()->json(['message' => 'Mensaje no encontrado'], 404);
        }

        $modal = modalservicios::find($modal_mail->id_modalservicio);

        try{
            $data = [
                'nombre' => $modal->nombre,
                'telefono' => $modal->telefono,
                'correo' => $modal->correo,
            ];

            Mail::to($modal->correo)->send(
                new MailService($modal_mail->number_message, $data, $modal->id_producto)
            );

            $modal_mail->update([
                'estado' => 1,
                'fecha' => now(),
            ]);

            return response()->json($modal_mail, 200);

        }catch(Exception $e){
            $modal_mail->update([
                'estado' => 1,
                'error' => 'Enviado con error, el correo no existe',
                'fecha' => now(),
            ]);
            return response()->json(['message' => 'Error al enviar el correo'], 500);
        }
    }

    public function reportarError(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'error' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $modal_mail = EmailModal::find($id);
        if (!$modal_mail) {
            return response()->json(['message' => 'Mensaje no encontrado'], 404);
        }

        $modal_mail->update([
            'estado' => 1,
            'error' => $request->error,
            'fecha' => now(),
        ]);

        return response()->json([
            'message' => 'Error reportado exitosamente',
            'modal_mail' => $modal_mail
        ], 200);
    }
}
