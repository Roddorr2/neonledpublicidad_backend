<?php

namespace App\Http\Controllers\Api;

use App\Mail\ModalMail;
use App\Models\WatModal;
use App\Mail\MailService;
use App\Models\EmailModal;
use Illuminate\Http\Request;
use App\Models\modalservicios;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
/**
 * @OA\Tag(
 *     name="Modales",
 *     description="Operaciones relacionadas a modales y envío de correos/WhatsApp"
 * )
 */
class ModalesController extends Controller
{

    /**
 * @OA\Get(
 *     path="/api/modales",
 *     summary="Listar todos los modales paginados",
 *     tags={"Modales"},
 *     @OA\Response(
 *         response=200,
 *         description="Listado exitoso de modales"
 *     )
 * )
 */
    public function get(Request $request)
    {
        $modals = modalservicios::with('servicio')->orderBy('id_modalservicio', 'asc')->paginate(4);

        return response()->json($modals, 200);
    }
/**
 * @OA\Get(
 *     path="/api/modales/send/{id}",
 *     summary="Obtener correos y WhatsApp programados de un modal",
 *     tags={"Modales"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del modal",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Datos encontrados")
 * )
 */
    public function getSendModales($id){

        $modals_mails = EmailModal::where('id_modalservicio', $id)->get();
        $modal_wats = WatModal::where('id_modalservicio', $id)->get();

        $data = [
            'mails' => $modals_mails,
            'wats' => $modal_wats,
            'status' => 200
        ];

        return response()->json($data, 200);
    }


    /**
 * @OA\Post(
 *     path="/api/modales",
 *     summary="Crear un nuevo modal y agendar correos/WhatsApp",
 *     tags={"Modales"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"nombre","telefono","correo","id_servicio"},
 *             @OA\Property(property="nombre", type="string", maxLength=100),
 *             @OA\Property(property="telefono", type="string", maxLength=9),
 *             @OA\Property(property="correo", type="string", format="email", maxLength=200),
 *             @OA\Property(property="id_servicio", type="integer", minimum=1, maximum=4)
 *         )
 *     ),
 *     @OA\Response(response=201, description="Modal creado exitosamente"),
 *     @OA\Response(response=400, description="Error en validación"),
 *     @OA\Response(response=500, description="Error interno al crear")
 * )
 */
    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:100',
                'telefono' => 'required|string|max:9',
                'correo' => 'required|email|max:200',
                'id_servicio' => 'required|integer|min:1|max:4',
            ]);

            DB::beginTransaction();

            $modal_servicio = modalservicios::create($request->all());

            for ($i = 1; $i <= 3; $i++) {
                if ($i == 1){
                    $first_email_modal = EmailModal::create([
                        'estado' => 0,
                        'error' => '',
                        'id_modalservicio' => $modal_servicio->id_modalservicio,
                        'number_message' => $i,
                        'fecha' => now(),
                    ]);
                }
                else{
                    EmailModal::create([
                        'estado' => 0,
                        'error' => '',
                        'id_modalservicio' => $modal_servicio->id_modalservicio,
                        'number_message' => $i,
                        'fecha' => now(),
                    ]);
                }
            }

            for ($i = 1; $i <= 2; $i++) {
                WatModal::create([
                    'estado' => 0,
                    'error' => '',
                    'id_modalservicio' => $modal_servicio->id_modalservicio,
                    'number_message' => $i,
                    'fecha' => now(),
                ]);
            }

            try{
                //aqui tratamos de enviar el email al correo correspondiente, ya que si no existe
                // el primer registro lo cambiamos con error de envio

                $data = [
                    'nombre' => $request->nombre,
                    'correo' => $request->correo,
                    'telefono' => $request->telefono
                ];

                Mail::to($request->correo)->send(
                    new MailService(1, $data, $request->id_servicio)
                );

                if (isset($first_email_modal)) {
                    $first_email_modal->update([
                        'estado' => 1,
                        'fecha' => now(),
                    ]);
                }

            }catch(\Exception $e){
                if (isset($first_email_modal)) {
                    $first_email_modal->update([
                        'estado' => 1,
                        'error' => 'Enviado con error, Posiblemente el correo no existe',
                        'fecha' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 201,
                'message' => 'Modal guardado exitosamente'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $error) {
            DB::rollback();
            return response()->json([
                'error' => 'Error en la validación',
                'details' => $error->errors()
            ], 400);
        } catch (\Exception $error) {
            DB::rollback();
            return response()->json([
                'error' => 'Error al crear el registro',
                'details' => $error->getMessage()
            ], 500);
        }
    }

    /**
 * @OA\Get(
 *     path="/api/modales/{id}",
 *     summary="Obtener modal por ID",
 *     tags={"Modales"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del modal",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Modal encontrado"),
 *     @OA\Response(response=404, description="Modal no encontrado")
 * )
 */
    public function getById($id)
    {
        $modal = modalservicios::where('id_modalservicio', $id)->with('servicio')->first();

        if (!$modal) {
            return response()->json(['error' => 'Modal no encontrado'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $modal
        ], 200);
    }

    /**
 * @OA\Put(
 *     path="/api/modales/{id}",
 *     summary="Actualizar estado del modal",
 *     tags={"Modales"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del modal",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"estado"},
 *             @OA\Property(property="estado", type="boolean")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Estado actualizado"),
 *     @OA\Response(response=404, description="Modal no encontrado")
 * )
 */
    public function update(Request $request, $id)
    {
        $modal = modalservicios::find($id);

        if (!$modal) {
            return response()->json(['error' => 'Modal no encontrado'], 404);
        }

        $validated = $request->validate([
            'estado' => 'required|boolean',
        ]);

        $modal->update([
            'estado' => $request->estado,
        ]);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data' => $modal,
        ], 200);
    }

    /**
 * @OA\Delete(
 *     path="/api/modales/{id}",
 *     summary="Eliminar un modal junto a sus correos y WhatsApp relacionados",
 *     tags={"Modales"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del modal",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Modal eliminado"),
 *     @OA\Response(response=404, description="Modal no encontrado")
 * )
 */
    public function delete($id)
    {
        $modal = modalservicios::find($id);

        if (!$modal) {
            return response()->json(['error' => 'Modal no encontrado'], 404);
        }

        $emails_modal = EmailModal::where('id_modalservicio', $id)->get();
        $wats_modal = WatModal::where('id_modalservicio', $id)->get();

        if($emails_modal != null){
            foreach ($emails_modal as $email) {
                $email->delete();
            }
        }

        if($wats_modal != null){
            foreach ($wats_modal as $wat) {
                $wat->delete();
            }
        }

        $modal->delete();

        return response()->json([
            'message' => 'Modal eliminado exitosamente'
        ], 200);
    }
}
