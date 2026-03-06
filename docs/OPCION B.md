1. Resumen técnico

Se implementará control de envíos WhatsApp con:

50 mensajes diarios para campañas

50 mensajes diarios para modales

Ventana de envío: 08:00 – 23:00 (America/Lima)

Sin campañas simultáneas

Sin tabla de reservas

Sin planner complejo

Uso de scheduled_at como sistema de planificación

El control diario se hará contando registros en modal_whats.

2. Arquitectura general

Separación clara:

Laravel:

Orquesta

Calcula scheduled_at

Controla límite diario

Crea registros en modal_whats

Ejecuta jobs por lote

Node (Baileys):

Envía mensajes

Aplica pacing interno (~1 min por mensaje)

Maneja reintentos técnicos

Base de datos:

modalservicios

modal_whats

campanias_whatsapp

No existe tabla de reservas.

3. Flujo general
MODALES

Usuario llena formulario

Se crea registro en modalservicios

Controller Modal:

Genera 3 registros en modal_whats

Calcula scheduled_at

Verifica límite diario (50)

Si hoy está lleno → programa siguiente día 08:00

Job Modal procesa mensajes pendientes por orden de scheduled_at

CAMPAÑAS

Usuario crea campaña en dashboard

Backend filtra números desde modalservicios

Se valida que no exista campaña en_proceso

Se crean registros en modal_whats con:

flow_type = campaign

scheduled_at calculado respetando 50/día

Job Campaign envía por lotes (20 por ejecución)

4. Tabla de responsabilidades
Componente	Responsabilidad
Frontend	Crear campaña / formulario
Laravel Controller Modal	Crear 3 colas y calcular schedule
Laravel Controller Campaign	Generar envíos masivos
Laravel Jobs	Procesar envíos pendientes
Node Service	Enviar mensaje y aplicar pacing
DB	Persistencia y control diario
5. Modelo de datos
modal_whats

Campos clave:

id

numero

mensaje

flow_type (modal / campaign)

campaign_id (nullable)

estado (pendiente, en_proceso, enviado, error)

scheduled_at

sent_at

error

campanias_whatsapp

id

estado (pendiente, en_proceso, completada, cancelada, error)

total_envios

enviados

fallidos

created_at

GESTIÓN DE ESTADOS

Campaña:

pendiente → en_proceso → completada
pendiente → cancelada
en_proceso → error

WatModal:

pendiente → en_proceso → enviado
pendiente → error


7. Lógica interna
configuración global
DAILY_LIMIT_CAMPAIGN = 50
DAILY_LIMIT_MODAL = 50
CHUNK_SIZE = 20
VENTANA_INICIO = 08:00
VENTANA_FIN = 23:00
TIMEZONE = America/Lima
Rate limiter

Se calcula con:

COUNT(*) 
FROM modal_whats 
WHERE DATE(scheduled_at) = hoy
AND flow_type = X

Si >= 50 → mover al siguiente día.

Delay global

Campañas:

2 minutos entre chunks

Modales:

inmediato

+30 min

+1 hora
(si no hay cupo → siguiente día 08:00)

Estrategia ante error

Node maneja reintentos técnicos.

Laravel:

Si falla definitivamente → estado = error

No reintenta automáticamente al día siguiente

Reintento manual desde dashboard

¿Se reintenta?

Sí, solo reintento técnico interno en Node.

No se crea nuevo registro.
No consume nuevo cupo.

¿Qué pasa si Node falla completamente?

Job marca todos los registros como error

Campaña pasa a estado error

Se permite reintento manual

8. Consideraciones técnicas
Idempotencia

Si se activa dos veces campaña:

Validar que estado != en_proceso

Si ya existen registros modal_whats con campaign_id → no recrearlos

Concurrencia

No hay paralelismo alto.
Un worker es suficiente.

Duplicidad de envío

Antes de enviar:

WHERE estado = pendiente
AND scheduled_at <= now()

Nunca reenviar estado enviado.

Escalabilidad futura

Si se supera 300 mensajes/día:

Introducir tabla de reservas

Separar planner

Implementar bloqueo por SELECT FOR UPDATE

Límite de funcionalidad

Máximo absoluto:
100 mensajes/día (50 campaign + 50 modal)

No soporta alta concurrencia.
No soporta múltiples instancias distribuidas.

9. Checklist implementación

 Agregar campo flow_type a modal_whats

 Agregar campo scheduled_at

 Ajustar controller modal

 Ajustar controller campaign

 Implementar validación no campaña simultánea

 Crear job CampaignProcessor

 Crear job ModalProcessor

 Implementar conteo diario antes de asignar schedule

 Probar con 60 registros campaña

 Probar con 20 registros modal mismo día

10. Estado actual

Sistema actual:

Envío directo sin control estricto diario

Sin ventana global estricta

Sin verificación fuerte de no simultaneidad

Estado tras implementación:

Control diario real

Sin sobreingeniería

Sin tabla de reservas

Arquitectura simple y mantenible

CONCLUSIÓN ARQUITECTÓNICA

Para tu límite actual:

✔ No usar PlannerService complejo
✔ No usar tabla de reservas
✔ No usar SELECT FOR UPDATE
✔ No usar sub-jobs por chunk

Solo:

scheduled_at

count diario

job que procese pendientes

Es suficiente, limpio y seguro para 100 mensajes/día.

Si quieres, ahora puedo:

Diseñarte el pseudo-código exacto del Controller Campaign

Diseñarte el pseudo-código exacto del Controller Modal

O revisar tu implementación actual y decirte exactamente qué eliminar.