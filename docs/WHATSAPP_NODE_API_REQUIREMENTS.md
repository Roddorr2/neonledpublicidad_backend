# WhatsApp Node.js API - Integration Requirements

**Version**: 1.0  
**Date**: March 26, 2026  
**Status**: Active Implementation

---

## Table of Contents

1. [Overview](#overview)
2. [API Endpoint Changes](#api-endpoint-changes)
3. [Payload Structure - Modal Messages](#payload-structure---modal-messages)
4. [Payload Structure - Campaign Messages](#payload-structure---campaign-messages)
5. [Webhook Event Structure](#webhook-event-structure)
6. [Error Handling & Retry Strategy](#error-handling--retry-strategy)
7. [Configuration Requirements](#configuration-requirements)
8. [Examples](#examples)

---

## Overview

This document describes the updated WhatsApp API integration between the Laravel backend and the Node.js WhatsApp service. The system now supports two message flows:

- **Modal Messages**: Individual user registrations → 3 separate messages with delays
- **Campaign Messages**: Bulk promotional messages → chunked delivery (50+ recipients per chunk)

Both flows now properly track webhook events for audit, retry logic, and health monitoring.

---

## API Endpoint Changes

### POST `/api/whatsapp/send-message-image`

#### Purpose
Send a single WhatsApp message with optional image (used by both modal and campaign flows)

#### Authentication
- Header: `X-API-Key: <API_KEY>`
- Must be configured in Laravel `config/services.php` under `services.whatsapp.apikey`

#### Location
- Base URL configured in Laravel: `config/services.whatsapp.url`
- Current: `http://localhost:3001` (or production equivalent)

---

## Payload Structure - Modal Messages

### Request Payload

```json
{
  "telefono": "987654321",
  "templateOption": 1,
  "nombre": "Juan Pérez",
  "fecha": "2026-03-26",
  "hora": "14:30",
  "productoName": "LETRAS DE ACRÍLICO",
  "image_url": "https://example.com/images/plantilla1.jpg"
}
```

### Field Descriptions

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `telefono` | string | ✅ | **Clean phone number (digits only, no +51 prefix)**. Node.js must format with Perú country code "51" | `"987654321"` |
| `templateOption` | integer | ✅ | Message template variant (1, 2, or 3). Each registration gets 3 messages. | `1` |
| `nombre` | string | ✅ | Customer name for personalization | `"Juan Pérez"` |
| `fecha` | string | ✅ | Current date in YYYY-MM-DD format | `"2026-03-26"` |
| `hora` | string | ✅ | Current time in HH:MM format | `"14:30"` |
| `productoName` | string | ✅ | Product name (from `productos.nombre`) | `"LETRAS DE ACRÍLICO"` |
| `image_url` | string | ✅ | Direct URL to WhatsApp template image | `"https://cdn.example.com/plantilla1.jpg"` |

### Phone Number Formatting

**Important**: The phone number is sent as **clean digits only** (no country code).

**Node.js Responsibility**:
- Input: `"987654321"` (9-13 digits)
- Processing:
  1. Clean: Remove all non-digit characters
  2. Add Perú code: `"51" + cleaned_digits`
  3. Final: `"51987654321"` (11-15 digits total)
- Validation: After adding "51", total length must be 10-15 digits

**Example Processing**:
```javascript
// Input from Laravel
const telefono = "987654321";  // or "987-654-321" or "+51 987654321"

// Node.js processing
const clean = telefono.replace(/[^0-9]/g, '');
const withCode = clean.startsWith('51') ? clean : '51' + clean;

// Validation
if (withCode.length < 10 || withCode.length > 15) {
  throw new Error(`Invalid phone format: ${withCode}`);
}

// Use for WhatsApp API
const finalPhone = withCode; // "51987654321"
```

### Response Expected

```json
{
  "success": true,
  "message_id": "wamid.ABC123DEF456",
  "status": "sent",
  "timestamp": "2026-03-26T14:35:22Z"
}
```

**Response Fields**:

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `message_id` / `id` | string | ✅ | WhatsApp API message ID. Must send to webhook. |
| `success` | boolean | ✅ | Operation success indicator |
| `status` | string | ⚠️ | Initial status (often "sent" or "queued"). Updates via webhook. |

---

## Payload Structure - Campaign Messages

### Request Payload

Campaign messages share the same endpoint but are called via `SendWhatsAppCampaignJob` which normalizes recipient data.

```json
{
  "telefono": "987654321",
  "templateOption": 1,
  "nombre": "Customer Name",
  "fecha": "2026-03-26",
  "hora": "14:30",
  "productoName": "LETRAS DE ALUMINIO DORADAS 3D",
  "image_url": "https://example.com/images/campaign-template.jpg"
}
```

**Key Difference**: Campaign messages are processed in bulk through `PlannerService` which chunks them (50 recipients per chunk) and dispatches separate jobs for each chunk.

---

## Webhook Event Structure

### Webhook Payload (Node.js → Laravel)

Node.js **MUST POST** to the webhook endpoint at:
```
POST https://laravel-backend/api/whatsapp/webhook
```

Required Headers:
```
Content-Type: application/json
X-Webhook-Signature: <HMAC_SIGNATURE> (optional, but recommended)
```

### Event Payload Structure

```json
{
  "message_id": "wamid.ABC123DEF456",
  "status": "delivered",
  "timestamp": "2026-03-26T14:35:45Z",
  "phone_number": "51987654321",
  "id_modalservicio": 42,
  "id_modal_wat": 15,
  "campania_id": null,
  "chunk_id": null,
  "raw": {
    "from": "51987654321",
    "message_id": "wamid.ABC123DEF456",
    "status": "delivered",
    "timestamp": 1648308945,
    "type": "message_status"
  }
}
```

### Webhook Field Mapping

| Field | Type | Required | Description | Used For |
|-------|------|----------|-------------|----------|
| `message_id` / `provider_message_id` | string | ✅ | WhatsApp message ID from initial send response | Deduplication + tracking |
| `status` | string | ✅ | Message status from WhatsApp (see Status Codes below) | State machine |
| `timestamp` | ISO8601 string | ✅ | When webhook was generated | Event audit |
| `phone_number` | string | ✅ | Recipient phone (with 51 code) | Logging only |
| `id_modalservicio` | integer | ⚠️ | Modal registrant ID (for modal messages **only**) | Links to `modalservicios` |
| `id_modal_wat` | integer | ⚠️ | Modal message tracking ID (for modal messages **only**) | Links to `modal_wats` table |
| `campania_id` | integer | ⚠️ | Campaign ID (for campaign messages **only**) | Links to `campanias_whatsapp` |
| `chunk_id` | integer | ⚠️ | Chunk ID (for campaign messages **only**) | Links to `whatsapp_chunks` |
| `raw` | object | ✅ | Full raw response from WhatsApp API | Audit trail + debugging |

### Status Codes

Node.js must send standard WhatsApp business API status values:

```
FINAL STATES (webhook must include):
- "sent"       → Message reached WhatsApp servers
- "delivered"  → Message received by device
- "read"       → User opened message
- "failed"     → Message failed to send
- "undelivered" → Message expired/unreachable
- "rejected"   → Rejected by WhatsApp
- "expired"    → Message TTL expired

INTERMEDIATE STATES (optional, telemetry only):
- "queued"     → Waiting in queue
- "sending"    → Being sent
- "accepted"   → WhatsApp confirmed receipt
- "processing" → Being processed
```

### Important: Modal vs Campaign Detection

**Laravel Backend Expectation**:

For **MODAL** messages:
```json
{
  "message_id": "wamid.XYZ",
  "status": "delivered",
  "id_modal_wat": 15,
  "id_modalservicio": 42
  // DO NOT include: campania_id, chunk_id
}
```

For **CAMPAIGN** messages:
```json
{
  "message_id": "wamid.XYZ",
  "status": "delivered",
  "campania_id": 12,
  "chunk_id": 8
  // DO NOT include: id_modal_wat, id_modalservicio
  // (or set them to null)
}
```

**Why?** Laravel's `ProcessWhatsappStatus` job reads `id_modal_wat` to determine flow:
- If `id_modal_wat` exists → Update individual `WatModal` record
- If `campania_id` exists → Update campaign counters + chunk status

---

## Error Handling & Retry Strategy

### Node.js Error Responses

When `/api/whatsapp/send-message-image` fails:

#### Status 400 - Bad Request
```json
{
  "error": "Invalid phone number format",
  "details": "Phone must be digits only"
}
```
**Laravel Action**: Mark modal/campaign as failed (no retry)

#### Status 429 - Rate Limited
```json
{
  "error": "Rate limit exceeded",
  "retry_after": 60
}
```
**Laravel Action**: Job executes with backoff (60s delay, max 3 retries)

#### Status 500 - Server Error
```json
{
  "error": "Internal server error",
  "message": "WhatsApp API timeout"
}
```
**Laravel Action**: Job retries with exponential backoff

#### Status 503 - Service Unavailable
```json
{
  "error": "WhatsApp service temporarily unavailable",
  "estimated_recovery": "2026-03-26T15:00:00Z"
}
```
**Laravel Action**: Job retries, eventually marked as "transported_failed"

### Webhook Event on Send Failure

When initial send fails with "transported_failed":

```json
{
  "message_id": "pending-uuid-12345",
  "status": "failed",
  "id_modal_wat": 15,
  "error_code": "1008",
  "error_message": "Unable to send message"
}
```

**Laravel Behavior**:
1. Stores event in `whatsapp_webhook_events` table
2. Sets `modal_wats.estado = -1` (error)
3. Sets `modal_wats.error = "WhatsApp status: failed"`
4. **Health check** can query this and retry later

---

## Configuration Requirements

### Laravel Backend Configuration

File: `config/services.php`

```php
'whatsapp' => [
    'url' => env('WHATSAPP_SERVICE_URL', 'http://localhost:3001'),
    'apikey' => env('WHATSAPP_API_KEY', 'your-secret-key'),
],
```

### Environment Variables

```env
# .env
WHATSAPP_SERVICE_URL=http://localhost:3001  # or production URL
WHATSAPP_API_KEY=sk_test_xyz123             # Secret key for X-API-Key header
WEBHOOK_URL=https://laravel-backend.com/api/whatsapp/webhook  # Node.js POSTs here
```

### Node.js Service Requirements

**Must Support**:
1. ✅ Receive POST requests on `/api/whatsapp/send-message-image`
2. ✅ Validate `X-API-Key` header
3. ✅ Format phone numbers with Perú code "51"
4. ✅ Return `message_id` in response
5. ✅ Send webhook events to Laravel webhook endpoint
6. ✅ Include proper status codes (400, 429, 500, 503 as needed)
7. ✅ Distinguish between modal and campaign webhooks (via IDs)

---

## Examples

### Example 1: Modal Message Send (Success Path)

#### Laravel → Node.js
```bash
POST http://localhost:3001/api/whatsapp/send-message-image
X-API-Key: sk_test_xyz123
Content-Type: application/json

{
  "telefono": "987654321",
  "templateOption": 1,
  "nombre": "Carlos López",
  "fecha": "2026-03-26",
  "hora": "10:15",
  "productoName": "LETRAS DE NEÓN LED",
  "image_url": "https://cdn.neonledpublicidad.com/plantilla-neon-1.jpg"
}
```

#### Node.js → Laravel (Response)
```json
{
  "success": true,
  "message_id": "wamid.HBEUGQiAgFQDAktmaBkJnKKQo_Q",
  "status": "sent",
  "timestamp": "2026-03-26T10:15:30Z"
}
```

#### Node.js → Laravel (Webhook, after ~5 seconds)
```bash
POST https://laravel-backend.com/api/whatsapp/webhook
Content-Type: application/json

{
  "message_id": "wamid.HBEUGQiAgFQDAktmaBkJnKKQo_Q",
  "status": "delivered",
  "timestamp": "2026-03-26T10:15:35Z",
  "phone_number": "51987654321",
  "id_modalservicio": 42,
  "id_modal_wat": 15,
  "raw": {
    "from": "51987654321",
    "message_id": "wamid.HBEUGQiAgFQDAktmaBkJnKKQo_Q",
    "status": "delivered",
    "timestamp": 1648305335,
    "type": "message_status"
  }
}
```

**Laravel Backend Processing**:
1. `SendWhatsAppJob` sends request
2. Stores `message_id` in response
3. Updates `modal_wats.estado = 1` (success)
4. Webhook arrives → `ProcessWhatsappStatus` updates `modal_wats.estado = 1, message_id = "wamid...", error = ""`

---

### Example 2: Modal Message Send (Failure Path)

#### Original Send Succeeds
```json
{
  "success": true,
  "message_id": "wamid.3KhwqQkVWQEDAkJnaBkJnKKQo_Y",
  "status": "sent"
}
```

#### Later, Webhook Reports Failure
```bash
POST https://laravel-backend.com/api/whatsapp/webhook

{
  "message_id": "wamid.3KhwqQkVWQEDAkJnaBkJnKKQo_Y",
  "status": "failed",
  "timestamp": "2026-03-26T10:20:15Z",
  "phone_number": "51987654321",
  "id_modal_wat": 15,
  "error_code": "1008",
  "error_message": "Unable to send message - recipient blocked"
}
```

**Laravel Backend Processing**:
1. `ProcessWhatsappStatus` receives webhook event
2. Detects `id_modal_wat = 15` (modal flow)
3. Updates `modal_wats`:
   - `estado = -1` (error)
   - `message_id = "wamid.3KhwqQkVWQEDAkJnaBkJnKKQo_Y"`
   - `error = "WhatsApp status: failed - recipient blocked"`
4. Stores full event in `whatsapp_webhook_events` for audit
5. Health check can later attempt retry

---

### Example 3: Campaign Message (Same Endpoint)

#### Laravel → Node.js
```json
{
  "telefono": "912345678",
  "templateOption": 1,
  "nombre": "Marketing Campaign Target",
  "fecha": "2026-03-26",
  "hora": "18:00",
  "productoName": "MONITORES DE PUBLICIDAD",
  "image_url": "https://cdn.neonledpublicidad.com/campaign-promo.jpg"
}
```

#### Node.js → Laravel (Webhook)
```json
{
  "message_id": "wamid.CmJnaBkJnKKQo_Z123",
  "status": "delivered",
  "timestamp": "2026-03-26T18:00:45Z",
  "phone_number": "51912345678",
  "campania_id": 5,
  "chunk_id": 12,
  "raw": { ... }
}
```

**Laravel Backend Processing**:
1. `ProcessWhatsappStatus` detects `campania_id = 5` (campaign flow)
2. **Does NOT** update `modal_wats`
3. Updates campaign counters:
   - `campanias_whatsapp.envios_exitosos += 1`
   - `campanias_whatsapp.envios_pendientes -= 1`
4. Checks if chunk 12 is complete (all 50 recipients have final status)
5. If yes, marks chunk as "completed" and checks if campaign is done

---

## Migration Summary

### Database Changes (Laravel)

**Table**: `modal_wats`
- **Added**: `id_plantilla_whatsapp` (FK to `plantillas_whatsapp`)
- **Added**: `message_id` (stores WhatsApp API response ID)
- **Added**: `attempts` (retry counter)
- **Removed**: `reservation_id`, `scheduled_at`, `flow_type`, `campaign_id` (unused)

**Table**: `whatsapp_webhook_events`
- **No changes to structure** (already supports both modal and campaign flows)
- Now actively used for modal events when `transported_failed` or other failures occur

---

## Rollout Checklist

- [ ] Node.js: Update `/api/whatsapp/send-message-image` to accept all 7 payload fields
- [ ] Node.js: Implement phone number formatting (clean input → add "51" code)
- [ ] Node.js: Validate total phone length (10-15 digits after adding "51")
- [ ] Node.js: Update webhook to send both modal and campaign event types
- [ ] Node.js: Ensure `message_id` is always returned in both send response and webhook
- [ ] Node.js: Configure webhook URL in environment variables
- [ ] Laravel: Run migrations (`php artisan migrate`)
- [ ] Laravel: Run seeders to populate test data
- [ ] Testing: Send modal message → verify webhook receipt → check `modal_wats` table
- [ ] Testing: Send campaign → verify chunk updates
- [ ] Monitoring: Configure logging for webhook events
- [ ] Documentation: Update API documentation with new fields

---

## Support & Debugging

### Common Issues

**Issue**: "El número debe tener entre 10 y 15 dígitos"
- **Cause**: Phone number validation failed after formatting
- **Solution**: Check Node.js is adding "51" correctly. Verify input length 9-13 digits.

**Issue**: Webhook events not reaching Laravel
- **Cause**: Webhook URL misconfigured or firewall blocking
- **Solution**: Check `config/services.php` has correct webhook URL. Test with curl from Node.js server.

**Issue**: `id_modal_wat` not updating
- **Cause**: Node.js not including `id_modal_wat` in webhook payload
- **Solution**: Verify Node.js stores modal IDs during send and includes them in webhook.

---

## References

- Laravel Job: `app/Jobs/SendWhatsAppJob.php`
- Webhook Processor: `app/Jobs/ProcessWhatsappStatus.php`
- Controller: `app/Http/Controllers/Api/ModalesController.php`
- Migration: `database/migrations/2026_02_26_000001_add_reservation_fields_to_modal_wats_table.php`

---

**Questions?** Contact backend team.  
**Last Updated**: March 26, 2026
