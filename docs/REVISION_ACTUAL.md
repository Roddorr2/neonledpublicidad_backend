# Revisión Actual - Modal Wats & Campaign WhatsApp

**Fecha**: March 26, 2026  
**Status**: Implementación Completada

---
# REVISION_ACTUAL — Contratos (Modal y Campaign)

**Fecha**: 2026-03-26
**Propósito**: Documento conciso con los contratos (request / response) que usan Laravel ↔ Node.js para los flujos Modal y Campaign.

---

## 1) Modal (individual)

- Crear modal

  POST /api/modales/create
  Content-Type: application/json

  Request JSON:
  {
    "nombre": "string",
    "telefono": "string",        // se envía tal cual está en BD
    "correo": "string (opcional)",
    "id_producto": number,
    "productoName": "string"
  }

  Response (201):
  {
    "success": true,
    "message": "string",
    "data": { "id_modalservicio": number }
  }

- Estado del modal

  GET /api/modales/sendmodales/{id}

  Response (200):
  {
    "mails": [],
    "wats": [
      {
        "id_modal_wat": number,
        "id_modalservicio": number,
        "estado": -1|0|1,
        "message_id": "string|null",
        "number_message": number,
        "error": "string",
        "fecha": "YYYY-MM-DD HH:MM:SS"
      }
    ]
  }

- Payload que Laravel envía a Node.js para cada intento (endpoint Node.js)

  POST /api/whatsapp/send-message-image
  Content-Type: application/json

  Request JSON (modal):
  {
    "telefono": "string",                // SIN formato; tal cual BD
    "nombre": "string",
    "mensaje": "string",                 // contenido tomado de PlantillaWhatsapp
    "image_url": "string|null",          // URL tomada de PlantillaWhatsapp
    "fecha": "YYYY-MM-DD",
    "hora": "HH:MM",
    "productoName": "string",
    "id_modal_wat": number,                // id del registro en modal_wats
    "id_plantilla_whatsapp": number|null
  }

  Response JSON (Node.js):
  {
    "success": true|false,
    "message_id": "string|null",
    "status": "queued|sent|delivered|failed|...",
    "timestamp": "ISO8601",
    "error_code": "string|null",
    "error_message": "string|null"
  }

- Webhook (Node.js → Laravel) — para estados finales o errores

  POST /api/whatsapp/webhook
  Content-Type: application/json

  Body:
  {
    "provider_message_id": "string",
    "status": "delivered|failed|...",
    "timestamp": "ISO8601",
    "phone_number": "string",
    "id_modal_wat": number,          // presente para modal
    "id_modalservicio": number,
    "error_code": "string|null",
    "error_message": "string|null",
    "raw": { /* objeto crudo del proveedor */ }
  }

  Laravel Response: HTTP 200 OK (body opcional: {"received":true})

---

## 2) Campaign (masivo)

- Crear campaña

  POST /api/whatsapp/campaign/create
  Content-Type: multipart/form-data

  Form fields:
  - service: string
  - paragraph: string
  - image: file (opcional)

  Response (201):
  {
    "success": true,
    "message": "string",
    "data": {
      "campania_id": number,
      "total_destinatarios": number,
      "estado": "borrador",
      "imagen_url": "string|null"
    }
  }

- Iniciar campaña

  POST /api/whatsapp/campaign/{id}/start
  Content-Type: application/json

  Response (200):
  {
    "success": true,
    "message": "string",
    "data": { "campania_id": number, "estado": "en_proceso", "total_destinatarios": number, "chunks": number }
  }

- Payload que Laravel envía a Node.js por cada destinatario (misma ruta Node.js)

  POST /api/whatsapp/send-message-image
  Content-Type: application/json

  Request JSON (campaign):
  {
    "telefono": "string",               // tal cual BD
    "nombre": "string",
    "mensaje": "string",                // paragraph o plantilla usada por la campaña
    "image_url": "string|null",
    "fecha": "YYYY-MM-DD",
    "hora": "HH:MM",
    "productoName": "string",
    "campania_id": number,
    "chunk_id": number,
    "recipient_id": number|null
  }

  Response JSON (Node.js): igual que Modal Response

- Webhook (Node.js → Laravel) — obligatorio por cada mensaje

  POST /api/whatsapp/webhook
  Body (campaign):
  {
    "provider_message_id": "string",
    "status": "delivered|failed|...",
    "timestamp": "ISO8601",
    "phone_number": "string",
    "campania_id": number,
    "chunk_id": number,
    "recipient_id": number|null,
    "error_code": "string|null",
    "error_message": "string|null",
    "raw": { }
  }

  Laravel Response: HTTP 200 OK

---

## Notas rápidas
- Teléfono: se envía desde Laravel tal cual está en BD; el servicio Node.js debe normalizar/formatear según proveedor.
- Para modal, el contenido `mensaje` e `image_url` deben provenir de `PlantillaWhatsapp` en el backend (no enviar `templateOption`).
- Para campaign, mantener el flujo actual de chunking; Node.js usa el mismo endpoint para enviar individualmente cada mensaje.

---

Archivo: [docs/REVISION_ACTUAL.md](docs/REVISION_ACTUAL.md)
```
campanias_whatsapp:
- envios_exitosos: + 1
- envios_pendientes: - 1

whatsapp_webhook_events:
- chunk_id: 1
- campania_id: 12
- provider_message_id: wamid.HBE_CAMPAIGN_001
- status: delivered
```

---

**Webhook 2 - Fallo:**

```json
{
  "message_id": "wamid.HBE_CAMPAIGN_002",
  "status": "failed",
  "timestamp": "2026-03-26T14:36:10Z",
  "phone_number": "51912345678",
  "campania_id": 12,
  "chunk_id": 1,
  "error_code": "1008"
}
```

**DB Update Automático:**
```
campanias_whatsapp:
- envios_fallidos: + 1
- envios_pendientes: - 1

whatsapp_webhook_events:
- chunk_id: 1
- campania_id: 12
- provider_message_id: wamid.HBE_CAMPAIGN_002
- status: failed
```

---

### 2.5 Monitorear Progreso - Endpoint

```
GET /api/whatsapp/campaign/12/status
Content-Type: application/json
```

**Response (Progress: 50%):**
```json
{
  "success": true,
  "data": {
    "id_campania": 12,
    "servicio": "LETRAS DE ACRÍLICO",
    "estado": "en_proceso",
    "progreso": {
      "total": 147,
      "exitosos": 72,
      "fallidos": 5,
      "pendientes": 70,
      "porcentaje": 52.38
    },
    "progress_milestone": 50,
    "progress_version": 5,
    "envios_hoy": 77,
    "limite_diario": 500,
    "fecha_inicio": "2026-03-26 14:30:00",
    "fecha_fin": null,
    "duracion": "En proceso"
  }
}
```

---

### 2.6 Chunks 2 y 3 Enviados (Igual Proceso)

**Chunk 2**: 50 mensajes enviados → 50 webhooks procesados

**Chunk 3**: 47 mensajes enviados → 47 webhooks procesados

---

### 2.7 Campaña Completada

**Cuando todos los chunks tienen estado final:**

```
POST https://laravel-backend.com/api/whatsapp/webhook
(último webhook)
```

**DB Update Final:**
```
campanias_whatsapp:
- estado: completada
- fecha_fin: 2026-03-26 16:45:20
- envios_exitosos: 142
- envios_fallidos: 5
- envios_pendientes: 0

whatsapp_chunks:
- status: completed (para chunks 1, 2, 3)
- completed_at: timestamps
```

---

### 2.8 Consultar Estado Final - Endpoint

```
GET /api/whatsapp/campaign/12/status
Content-Type: application/json
```

**Response (100% Complete):**
```json
{
  "success": true,
  "data": {
    "id_campania": 12,
    "servicio": "LETRAS DE ACRÍLICO",
    "estado": "completada",
    "progreso": {
      "total": 147,
      "exitosos": 142,
      "fallidos": 5,
      "pendientes": 0,
      "porcentaje": 100
    },
    "progress_milestone": 100,
    "progress_version": 12,
    "fecha_inicio": "2026-03-26 14:30:00",
    "fecha_fin": "2026-03-26 16:45:20",
    "duracion": "2 hours"
  }
}
```

---

### 2.9 Listar Todas las Campañas - Endpoint

```
GET /api/whatsapp/campaigns?estado=completada&per_page=10
Content-Type: application/json
```

**Response:**
```json
{
  "success": true,
  "active_campaign": null,
  "data": {
    "campanias": [
      {
        "id_campania": 12,
        "servicio": "LETRAS DE ACRÍLICO",
        "estado": "completada",
        "total_destinatarios": 147,
        "envios_exitosos": 142,
        "envios_fallidos": 5,
        "envios_pendientes": 0,
        "porcentaje": 100,
        "progress_milestone": 100,
        "fecha_inicio": "2026-03-26 14:30:00",
        "fecha_fin": "2026-03-26 16:45:20"
      }
    ]
  },
  "pagination": {
    "total": 1,
    "per_page": 10,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

---

## 📊 TABLA COMPARATIVA

| Aspecto | **Modal Wats** | **Campaign** |
|--------|----------------|-----------|
| **Crear Endpoint** | `POST /api/modales/create` | `POST /api/whatsapp/campaign/create` |
| **Iniciar Endpoint** | Auto (en create) | `POST /api/whatsapp/campaign/{id}/start` |
| **Destinatarios** | 1 | Muchos (100+) |
| **Mensajes/Destino** | 3 | 1 |
| **Delays** | 0min, +30min, +1h | Inmediato |
| **Node.js Endpoint** | `/api/whatsapp/send-message-image` | `/api/whatsapp/send-message-image` |
| **Payload JSON** | Idéntico | Idéntico |
| **Tabla Tracking** | `modal_wats` | `whatsapp_chunks` |
| **Contador BD** | `estado` (1/-1/0) | `envios_exitosos`, `envios_fallidos` |
| **Webhooks** | Opcional (si falla) | Obligatorio (cada mensaje) |

---

## 📋 RESUMEN ENDPOINTS

### Modal
```
POST   /api/modales/create                    Crear + disparar 3 jobs
GET    /api/modales/sendmodales/{id}          Ver estado de envíos
```

### Campaign
```
POST   /api/whatsapp/campaign/create          Crear en borrador
POST   /api/whatsapp/campaign/{id}/start      Iniciar (FIFO)
GET    /api/whatsapp/campaign/{id}/status     Status en tiempo real
GET    /api/whatsapp/campaigns                Listar todas
POST   /api/whatsapp/campaign/estimate        Estimar duración
```

### Node.js
```
POST   /api/whatsapp/send-message-image       Enviar mensaje
POST   /api/whatsapp/webhook                  Recibir eventos
```

---

**Última Actualización**: March 26, 2026
