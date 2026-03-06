## Plan — Fase 0: Envío por lotes WhatsApp (reservas y sub-jobs)

Resumen: implementar la infraestructura mínima para reservar slots diarios (50/día), planificar chunks y despachar sub-jobs por chunk con spacing de 2 minutos.

### Supuestos
- `chunk_size`: 20
- `daily_limit`: 50 mensajes/día por campaña
- Las 3 entradas de `WatModal` por registro cuentan dentro del límite secundario
- Los `WatModal` gestionan su propio timing; las campañas usan delay de 2 minutos entre chunks
 - Zona horaria global: `America/Lima` (Perú)
 - Ventana de envío global: `08:00` — `23:00` (hora local Peru). El planner debe respetar esta ventana al asignar `scheduled_at`.
 - Reservas para `WatModal`: al crear un registro de `modalservicios` el controller intentará reservar inmediatamente los 3 slots (greedy same-day) vinculando las entradas a la reserva; si no hay suficiente cupo hoy, completar en siguiente día disponible.

### Tareas (lista priorizada)

1. Crear migración `whatsapp_campaign_reservations`
   - columnas mínimas: `id`, `campaign_id`, `date` (Y-m-d), `reserved_slots`, `created_at`, `updated_at`
   - comando: `php artisan make:migration create_whatsapp_campaign_reservations_table --create=whatsapp_campaign_reservations`

2. Crear modelo `WhatsappCampaignReservation`
   - implementar método estático `reserveSlots($campaignId, Carbon $date, int $n)` que: 1) abre transacción, 2) bloquea fila `SELECT FOR UPDATE`, 3) comprueba cupo (<=50), 4) incrementa `reserved_slots` y devuelve el registro o `false`.

   - `reserveSlots` debe respetar la ventana global (no reservar fuera de 08:00-23:00) y operar en la zona `America/Lima`.

3. Helper/Service: `PlannerService::planCampaign(Campania $campaign, Carbon $startDate = null)`
   - generar chunks (`array_chunk` sobre destinatarios / `WatModal` cuando aplique)
   - para cada chunk intentar `reserveSlots` en el mismo día (greedy). Si no hay cupo, probar siguiente día dentro de la ventana.
   - al reservar, calcular `delay` relativo dentro del día: `delayMinutes = chunkIndexInDay * 2` y `dispatch(new SendWhatsAppChunkJob(...))->delay($when)`.

      - El cálculo de `delay` debe producir `scheduled_at` dentro de la ventana 08:00–23:00; si el `delay` excede la ventana, desplazar al siguiente día disponible.

4. Implementar `SendWhatsAppChunkJob` (sub-job mínimo)
   - firma: `__construct($campaignId, array $chunk, $reservationId)`
   - validar reservación y marcaje idempotente (ej. `reserved = true` en `WatModal`) antes de enviar
   - llamar a `whatsapp-service` con payload
   - en éxito: actualizar `WatModal` (`estado = 1`, `fecha`) y contadores en `campanias_whatsapp`
   - en fallo permanente: marcar `WatModal` con `estado = 0` y `error`; en caso de reprogramación, usar `reserveSlots` para la nueva fecha antes de dispatch

5. Ajustar `SendWhatsAppCampaignJob`
   - delegar a `PlannerService::planCampaign()` en vez de ejecutar envíos directamente
   - mantener `public $tries` si se desea; no usar backoff exponencial (policy: dispatch con delays de 2 min por chunk)

6. Integración con `WatModal`
   - asegurar que el planner consulte y contabilice `WatModal` existentes (las 3 colas por modal)
   - marcar `WatModal` como `reserved` vinculándolos a `reservation_id` para evitar doble envío

7. Pruebas y validación
   - migrar DB: `php artisan migrate`
   - test concurrente de reservas (simular N procesos intentando reservar el mismo día)
   - test funcional: crear campaña con >50 destinatarios y verificar distribución, delays y actualizaciones de `campanias_whatsapp`

8. Observabilidad y operaciones
   - logs por `reservation_id` y `chunk_id`
   - métricas: `envios_exitosos`, `envios_fallidos`, `envios_pendientes` por campaña y por día
   - monitorizar `failed_jobs` y tabla `whatsapp_campaign_reservations`

### Criterios de aceptación (mínimos)
- Reservas atómicas evitadas sobresuscripción en escenarios concurrentes
- Chunks despachados con delays de 2 minutos entre ellos en el mismo día
- `WatModal` contabilizados y actualizados (`estado`, `error`) según resultado
- Campaña marca `completada` al terminar o `pendiente_parcial` si no hay cupo dentro de la ventana

---
Si confirmas, implemento en este orden: migración + modelo -> helper `reserveSlots` -> `PlannerService` -> `SendWhatsAppChunkJob` -> ajustes y pruebas.
