<?php

namespace App\Http\Controllers\Api;

use App\Mail\ModalMail;
use App\Models\WatModal;
use App\Mail\MailService;
use App\Models\EmailModal;
use Illuminate\Http\Request;
use App\Models\modalservicios;
use App\Models\PlantillaWhatsapp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendEmailJob;
use App\Jobs\SendWhatsAppJob;

class ModalesController extends Controller
{
    public function get(Request $request)
    {
        // $modals = modalservicios::with('servicio')->orderBy('id_modalservicio', 'asc')->paginate(4);
        $modals = Modalservicios::orderBy('id_modalservicio', 'asc')->paginate(4);

        return response()->json($modals, 200);
    }

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

    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:100',
                'telefono' => 'required|string|max:9',
                'correo' => 'required|email|max:200',
                'id_producto' => 'required|integer|exists:productos,id_producto',
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

            for ($i = 1; $i <= 3; $i++) {
                // Buscar plantilla para este producto y número de mensaje
                $plantilla = PlantillaWhatsapp::where('id_producto', $request->id_producto)
                    ->where('numero_plantilla', $i)
                    ->first();
                
                WatModal::create([
                    'estado' => 0,
                    'error' => '',
                    'id_modalservicio' => $modal_servicio->id_modalservicio,
                    'number_message' => $i,
                    'fecha' => now(),
                    'id_plantilla_whatsapp' => $plantilla?->id_plantilla_whatsapp,
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

                $productoName = $request->productoName ?? '';

                //AQUI SE ENVÍA EL PRIMER CORREO (inmediato)
                dispatch(new SendEmailJob($request->correo, $data, $request->id_producto,1));

                //AQUI SE ENVÍA EL SEGUNDO CORREO (+2 días después)
                dispatch(new SendEmailJob($request->correo, $data, $request->id_producto,2))
                        ->delay(now()->addDays(2));
                        // ->delay(now()->addMinutes(2));

                //AQUI SE ENVÍA EL TERCER CORREO (+4 días después)
                dispatch(new SendEmailJob($request->correo, $data, $request->id_producto,3))
                        ->delay(now()->addDays(4));
                        // ->delay(now()->addMinutes(4));

                $wat1 = WatModal::where('id_modalservicio', $modal_servicio->id_modalservicio)
                    ->where('number_message', 1)
                    ->first();

                dispatch(new SendWhatsAppJob($wat1, $data, $productoName));

                $wat2 = WatModal::where('id_modalservicio', $modal_servicio->id_modalservicio)
                    ->where('number_message', 2)
                    ->first();

                dispatch(new SendWhatsAppJob($wat2, $data, $productoName))
                    ->delay(now()->addMinutes(30));

                $wat3 = WatModal::where('id_modalservicio', $modal_servicio->id_modalservicio)
                    ->where('number_message', 3)
                    ->first();

                dispatch(new SendWhatsAppJob($wat3, $data, $productoName))
                    ->delay(now()->addHours(1));



                if (isset($first_email_modal)) {
                    $first_email_modal->update([
                        'estado' => 1,
                        'fecha' => now(),
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Error dispatching modal messages', [
                    'error' => $e->getMessage(),
                    'modal_servicio_id' => $modal_servicio->id_modalservicio ?? null,
                ]);

                if (isset($first_email_modal)) {
                    $first_email_modal->update([
                        'estado' => 0,
                        'error' => 'Enviado con error: ' . $e->getMessage(),
                        'fecha' => now(),
                    ]);
                }

                // Marcar los wat modals pendientes con el error para seguimiento
                WatModal::where('id_modalservicio', $modal_servicio->id_modalservicio)
                    ->where('estado', 0)
                    ->update([
                        'error' => 'Dispatch error: ' . $e->getMessage(),
                        'fecha' => now(),
                    ]);
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

    public function getById($id)
    {
        // $modal = modalservicios::where('id_modalservicio', $id)->with('servicio')->first();
        $modal = Modalservicios::where('id_modalservicio', $id)->first();

        if (!$modal) {
            return response()->json(['error' => 'Modal no encontrado'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $modal
        ], 200);
    }

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

    public function delete($id)
    {
        $modal = modalservicios::find($id);

        if (!$modal) {
            return response()->json(['error' => 'Modal no encontrado'], 404);
        }

        EmailModal::where('id_modalservicio', $id)
            ->get()
            ->each(function (EmailModal $email): void {
                $email->delete();
            });

        WatModal::where('id_modalservicio', $id)
            ->get()
            ->each(function (WatModal $wat): void {
                $wat->delete();
            });

        $modal->delete();

        return response()->json([
            'message' => 'Modal eliminado exitosamente'
        ], 200);
    }
}
