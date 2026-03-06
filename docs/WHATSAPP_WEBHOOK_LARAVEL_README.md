# Integración de Webhooks WhatsApp — Laravel

Este documento explica cómo Laravel debe recibir, validar, encolar y procesar los webhooks que provienen del servicio Node/WhatsApp (envíos por mensaje o por chunk). Está pensado para integrarse con la tabla `modal_wats` y las reservas (`whatsapp_campaign_reservations`).

## Resumen
- El servicio WhatsApp debe enviar eventos por mensaje (`sent`, `failed`, `queued`) hacia Laravel mediante webhooks HTTP.
- Laravel acepta el webhook rápidamente (202/200), valida seguridad, encola un Job para procesar la actualización de la base de datos y responde inmediatamente.
- Todo procesamiento DB debe ser atómico e idempotente.

## Endpoint recomendado
- Ruta: `POST /api/whatsapp/webhook/status`
- Autenticación: `X-API-Key` o `X-Signature` (HMAC)

## Payload esperado (ejemplo)

```json
{
  "jobId": "abcd-1234",
  "campania_id": 123,
  "id_modalservicio": 987,
  "telefono": "+54911xxxxxxx",
  "status": "sent",
  "message_id": "BAE123...",
  "error": null,
  "sentAt": "2026-02-28T12:34:56Z"
}
```

Campos clave:
- `id_modalservicio` / `wat_id`: identificador idempotente vinculado a `modal_wats`.
- `status`: `sent` | `failed` | `queued`.
- `message_id`: id provisto por WhatsApp (cuando aplique).

## Respuesta inmediata
- Tras validar headers y JSON, devolver `202 Accepted` con un body mínimo `{ "accepted": true, "jobId": "abcd-1234" }`.
- No ejecutar actualizaciones pesadas sin encolar (evitar timeouts y latencia para el servicio remoto).

## Encolado asíncrono (Laravel Queues)
- Dispatchar un Job (`ProcessWhatsappStatus`) en una cola dedicada (`whatsapp-status`).
- El Job realiza la actualización atómica en BD.

### Código de ejemplo (controlador)

```php
// routes/api.php
Route::post('whatsapp/webhook/status', 'WhatsappWebhookController@status');

// app/Http/Controllers/WhatsappWebhookController.php
public function status(Request $req)
{
    if ($req->header('X-API-Key') !== config('services.whatsapp.api_key')) {
        return response()->json(['error'=>'Unauthorized'], 401);
    }

    $data = $req->validate([
      'id_modalservicio' => 'required',
      'status' => 'required|string',
      'message_id' => 'nullable|string',
      'sentAt' => 'nullable|date',
      'error' => 'nullable|string',
      'jobId' => 'nullable|string'
    ]);

    ProcessWhatsappStatus::dispatch($data)->onQueue('whatsapp-status');

    return response()->json(['accepted' => true], 202);
}
```

### Código de ejemplo (Job)

```php
// app/Jobs/ProcessWhatsappStatus.php
public function handle()
{
    $data = $this->data;
    DB::transaction(function() use ($data) {
        $mw = ModalWat::where('id_modalservicio', $data['id_modalservicio'])->lockForUpdate()->first();
        if (!$mw) return;

        // Idempotencia
        if (!empty($mw->message_id) && isset($data['message_id']) && $data['message_id'] === $mw->message_id) return;

        if ($data['status'] === 'sent') {
            $mw->status = 'sent';
            $mw->message_id = $data['message_id'] ?? $mw->message_id;
            $mw->sent_at = $data['sentAt'] ?? now();
            $mw->attempts = $mw->attempts ?? 1;
        } else {
            $mw->status = 'failed';
            $mw->error = $data['error'] ?? 'unknown';
            $mw->attempts = ($mw->attempts ?? 0) + 1;
        }
        $mw->save();
    });
}
```

## Idempotencia y locking
- Usar `lockForUpdate()` (SELECT ... FOR UPDATE) para bloquear la fila mientras se actualiza.
- Comprobar si `message_id` ya existe para evitar duplicados.
- Alternativamente usar columnas `message_id` y `status` con índices para acelerar búsquedas.

## Manejo de reservas (`whatsapp_campaign_reservations`)
- Laravel es la fuente de la verdad de las reservas.
- Al procesar `sent`, confirmar/ajustar la reserva en la misma transacción que actualiza `modal_wats` si es necesario.

## Observabilidad y métricas
- Log estructurado: incluir `jobId`, `campania_id`, `id_modalservicio`, `status`, `error`.
- Exponer métricas: `whatsapp_messages_sent_total`, `whatsapp_messages_failed_total`, latencias de procesamiento.

## Retries y reconciliación
- Dejar que la queue gestione reintentos transitorios.
- Proveer endpoint de reconciliación: `GET /api/whatsapp/job/{jobId}/status` que consulte DB para estado final.

## Webhooks por mensaje vs resumen por chunk
- Si el servicio envía webhooks por cada mensaje: procesar como arriba (job por mensaje o job batch que actualice muchas filas).
- Si el servicio envía resumen por chunk: encolar un job batch que recorre las entradas y actualiza cada `modal_wats` dentro de una transacción por fila o en batch.

## Testing
- Crear rutas de prueba en Laravel que acepten payloads de éxito/parcial/fallo para validar el flujo completo.
- Tests recomendados: caso idempotente (misma `message_id`), fallo transitorio (retry), fallo permanente (incrementar attempts y marcar failed).

## Recomendaciones finales
- Responder rápido (`202`) y encolar.
- Mantener idempotencia fuerte en DB.
- Registrar `jobId` y `campania_id` para trazabilidad.
- Implementar TTL/cleanup para jobs antiguos y registros temporales.

---

Archivo generado automáticamente por el asistente de integración.
