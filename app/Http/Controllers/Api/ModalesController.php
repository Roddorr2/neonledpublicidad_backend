<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modal\StoreModalRequest;
use App\Http\Requests\Modal\UpdateModalRequest;
use App\Jobs\SendEmailJob;
use App\Jobs\SendWhatsAppJob;
use App\Models\EmailModal;
use App\Models\modalservicios;
use App\Models\PlantillaWhatsapp;
use App\Models\WatModal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModalesController extends Controller
{
    public function get(Request $request)
    {
        $modals = modalservicios::orderBy('id_modalservicio', 'asc')->paginate(4);

        return response()->json($modals, 200);
    }

    public function getSendModales(int $id)
    {
        $modals_mails = EmailModal::where('id_modalservicio', $id)->get();
        $modal_wats   = WatModal::where('id_modalservicio', $id)->get();

        return response()->json([
            'mails'  => $modals_mails,
            'wats'   => $modal_wats,
            'status' => 200,
        ], 200);
    }

    public function create(StoreModalRequest $request)
    {
        try {
            DB::beginTransaction();

            $modal_servicio = modalservicios::create($request->all());

            for ($i = 1; $i <= 3; $i++) {
                $emailData = [
                    'estado'           => 0,
                    'error'            => '',
                    'id_modalservicio' => $modal_servicio->id_modalservicio,
                    'number_message'   => $i,
                    'fecha'            => now(),
                ];

                if ($i == 1) {
                    $first_email_modal = EmailModal::create($emailData);
                } else {
                    EmailModal::create($emailData);
                }
            }

            for ($i = 1; $i <= 3; $i++) {
                $plantilla = PlantillaWhatsapp::where('id_producto', $request->id_producto)
                    ->where('numero_plantilla', $i)
                    ->first();

                WatModal::create([
                    'estado'                => 0,
                    'error'                 => '',
                    'id_modalservicio'      => $modal_servicio->id_modalservicio,
                    'number_message'        => $i,
                    'fecha'                 => now(),
                    'id_plantilla_whatsapp' => $plantilla?->id_plantilla_whatsapp,
                ]);
            }

            try {
                $data = [
                    'nombre'   => $request->nombre,
                    'correo'   => $request->correo,
                    'telefono' => $request->telefono,
                ];

                $productoName = $request->productoName ?? '';

                dispatch(new SendEmailJob($request->correo, $data, $request->id_producto, 1));
                dispatch(new SendEmailJob($request->correo, $data, $request->id_producto, 2))->delay(now()->addDays(2));
                dispatch(new SendEmailJob($request->correo, $data, $request->id_producto, 3))->delay(now()->addDays(4));

                $wat1 = WatModal::where('id_modalservicio', $modal_servicio->id_modalservicio)->where('number_message', 1)->first();
                dispatch(new SendWhatsAppJob($wat1, $data, $productoName));

                $wat2 = WatModal::where('id_modalservicio', $modal_servicio->id_modalservicio)->where('number_message', 2)->first();
                dispatch(new SendWhatsAppJob($wat2, $data, $productoName))->delay(now()->addMinutes(30));

                $wat3 = WatModal::where('id_modalservicio', $modal_servicio->id_modalservicio)->where('number_message', 3)->first();
                dispatch(new SendWhatsAppJob($wat3, $data, $productoName))->delay(now()->addHours(1));

                if (isset($first_email_modal)) {
                    $first_email_modal->update(['estado' => 1, 'fecha' => now()]);
                }

            } catch (\Exception $e) {
                Log::error('Error dispatching modal messages', [
                    'error'             => $e->getMessage(),
                    'modal_servicio_id' => $modal_servicio->id_modalservicio ?? null,
                ]);

                if (isset($first_email_modal)) {
                    $first_email_modal->update([
                        'estado' => 0,
                        'error'  => 'Enviado con error: ' . $e->getMessage(),
                        'fecha'  => now(),
                    ]);
                }

                WatModal::where('id_modalservicio', $modal_servicio->id_modalservicio)
                    ->where('estado', 0)
                    ->update(['error' => 'Dispatch error: ' . $e->getMessage(), 'fecha' => now()]);
            }

            DB::commit();

            return response()->json([
                'status'  => 201,
                'message' => 'Modal guardado exitosamente',
            ], 201);

        } catch (\Exception $error) {
            DB::rollback();

            return response()->json([
                'error'   => 'Error al crear el registro',
                'details' => $error->getMessage(),
            ], 500);
        }
    }

    public function getById(int $id)
    {
        $modal = modalservicios::where('id_modalservicio', $id)->first();

        if (! $modal) {
            return response()->json(['error' => 'Modal no encontrado'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $modal], 200);
    }

    public function update(UpdateModalRequest $request, int $id)
    {
        $modal = modalservicios::find($id);

        if (! $modal) {
            return response()->json(['error' => 'Modal no encontrado'], 404);
        }

        $modal->update(['estado' => $request->estado]);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data'    => $modal,
        ], 200);
    }

    public function delete(int $id)
    {
        $modal = modalservicios::find($id);

        if (! $modal) {
            return response()->json(['error' => 'Modal no encontrado'], 404);
        }

        EmailModal::where('id_modalservicio', $id)->get()->each(function (EmailModal $email): void {
            $email->delete();
        });

        WatModal::where('id_modalservicio', $id)->get()->each(function (WatModal $wat): void {
            $wat->delete();
        });

        $modal->delete();

        return response()->json(['message' => 'Modal eliminado exitosamente'], 200);
    }
}
