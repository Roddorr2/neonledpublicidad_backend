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
/**
 * @OA\Tag(
 *     name="ModalMail",
 *     description="Operaciones relacionadas con el envío de correos y el reporte de errores desde los modales de servicios"
 * )
 */
class ModalMailController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/modal-mail/send/{id}",
     *     summary="Envía un correo electrónico asociado al modal de servicio",
     *     tags={"ModalMail"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del EmailModal",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Correo enviado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/EmailModal")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mensaje no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al enviar el correo"
     *     )
     * )
     */
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
                new MailService($modal_mail->number_message, $data, $modal->id_servicio)
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

      /**
     * @OA\Post(
     *     path="/api/modal-mail/report-error/{id}",
     *     summary="Reporta un error al enviar un correo",
     *     tags={"ModalMail"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del EmailModal",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"error"},
     *             @OA\Property(property="error", type="string", maxLength=500, example="Correo no válido")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Error reportado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error reportado exitosamente"),
     *             @OA\Property(property="modal_mail", ref="#/components/schemas/EmailModal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos inválidos"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mensaje no encontrado"
     *     )
     * )
     */
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
