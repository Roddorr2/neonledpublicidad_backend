# API Key Authentication Standard - x-api-key

## Estándar de Autenticación

Ambos sistemas (Laravel Backend y Node.js WhatsApp Service) usan el **header `X-API-Key`** como estándar de industria para autenticación basada en API Key.

### Convención de Nombres
- **HTTP Header**: `X-API-Key` (con capitalización de convención)
- **Express/Node.js**: Normalizado automáticamente a `x-api-key` (lowercase) en `req.headers`
- **Laravel HTTP Client**: Enviado como `X-API-Key` en la cabecera

### Valor de la API Key

La API Key se obtiene de variables de entorno que **DEBEN coincidir exactamente**:

| Sistema | Variable | Valor | Ubicación |
|---------|----------|-------|-----------|
| **Laravel Backend** | `WHATSAPP_SERVICE_API_KEY` | `dev_local_2026_digimedia` | `.env` |
| **Node.js WhatsApp** | `API_KEY` | `dev_local_2026_digimedia` | `.env` |

⚠️ **CRÍTICO**: Los valores DEBEN ser idénticos. Si no coinciden, la autenticación fallará con `HTTP 401`.

---

## Implementación

### En Laravel (archivo que envía)

**Archivo**: `app/Services/WhatsappHealthService.php`

```php
$apiKey = config('services.whatsapp.apikey'); // Lee WHATSAPP_SERVICE_API_KEY
$response = Http::withHeaders(['X-API-Key' => $apiKey])
    ->post($url, []);
```

**Archivo**: `app/Console/Commands/OrchestrateWhatsappCampaigns.php`

```php
$apiKey = config('services.whatsapp.apikey');
$response = Http::withHeaders(['X-API-Key' => $apiKey])->post($url, $payload);
```

### En Node.js (archivo que valida)

**Archivo**: `src/middlewares/auth.middleware.js`

```javascript
export async function authenticateJWTorAPIKey(req, res, next) {
  const apiKey = req.headers['x-api-key']; // Express normaliza a lowercase
  
  if (apiKey) {
    if (!process.env.API_KEY || apiKey !== process.env.API_KEY) {
      return res.status(401).json({ success: false, message: 'API Key inválida' });
    }
    req.user = {
      userId: 'apiKeyUser',
      username: 'apiKeyUser',
      role: 'system',
      isSystemJob: true
    };
    return next();
  }
  
  return res.status(401).json({ success: false, message: 'Se requiere autenticación' });
}
```

---

## Endpoints Protegidos

Los siguientes endpoints en Node.js requieren autenticación con `X-API-Key`:

| Endpoint | Método | Requiere API Key |
|----------|--------|------------------|
| `/api/whatsapp/health` | POST | ✅ SÍ |
| `/api/whatsapp/send-campaign-batch` | POST | ✅ SÍ |
| `/api/whatsapp/send-message-image` | POST | ✅ SÍ |
| `/api/whatsapp/status` | GET | ✅ SÍ |
| `/api/whatsapp/sent-messages` | GET | ✅ SÍ |

---

## Testing

Para verificar que el endpoint `/api/whatsapp/health` responde correctamente:

```bash
# Desde Laravel project directory
php scripts/test_health_endpoint.php
```

Salida esperada:
```
✅ Health check PASSED: Service ready for sending
   - connected: true
   - apiKeyValid: true
   - webhooksOperational: true
```

### Prueba Manual con cURL

```bash
curl -X POST http://localhost:5111/api/whatsapp/health \
  -H "Content-Type: application/json" \
  -H "X-API-Key: dev_local_2026_digimedia" \
  -d '{}'
```

Respuestas posibles:

| Código | Significado | Acción |
|--------|------------|--------|
| **200** | Service healthy | Proceder a enviar mensajes |
| **401** | API Key inválida | Verificar `WHATSAPP_SERVICE_API_KEY` en `.env` |
| **404** | Endpoint no encontrado | Node.js no ha sido reiniciado (cargó código antiguo) |
| **503** | Service unavailable | WhatsApp no está conectado o webhooks caídos |

---

## Troubleshooting

### ❌ HTTP 404 - Endpoint not found
**Causa**: Node.js no ha sido reiniciado después de agregar el endpoint
**Solución**: 
```batch
taskkill /PID <pid> /F
cd c:\Users\axtev\Documents\TECHNOLOGY\WHATSAPP\whatsapp-service-nlp
npm start
```

### ❌ HTTP 401 - Unauthorized
**Causa**: API Key no coincide
**Verificar**:
1. `WHATSAPP_SERVICE_API_KEY` en Laravel `.env`
2. `API_KEY` en Node.js `.env`
3. Ambos deben tener exactamente el valor: `dev_local_2026_digimedia`

### ❌ HTTP 503 - Service Unavailable
**Causa**: WhatsApp no está conectado o webhooks caídos
**Acciones**:
1. Verificar WhatsApp QR code en dashboard
2. Escanear QR si es necesario
3. Verificar conexión socket en Node.js logs

---

## Configuración de Variables de Entorno

### Laravel (.env)
```bash
WHATSAPP_API_URL=http://localhost:5111
WHATSAPP_SERVICE_API_KEY=dev_local_2026_digimedia
```

### Node.js (.env)
```bash
API_KEY=dev_local_2026_digimedia
MAIN_BACKEND_URL=http://127.0.0.1:8000
```

**Importante**: Estos valores deben estar sincronizados en ambos archivos `.env`.

---

## Flujo de Autenticación

```
┌──────────────────────────────────────────────┐
│   Laravel Backend queiere enviar campaña     │
└──────────────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────┐
│  OrchestrateWhatsappCampaigns verifica salud │
│  llama WhatsappHealthService::checkHealth()  │
└──────────────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────┐
│    POST http://localhost:5111/api/whatsapp   │
│            /health                           │
│    Header: X-API-Key: dev_local_2026...      │
└──────────────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────┐
│   Node.js Express App recibe request         │
└──────────────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────┐
│  Middleware: authenticateJWTorAPIKey         │
│  - Lee x-api-key del header                  │
│  - Valida contra process.env.API_KEY         │
│  - Si no coincide: HTTP 401                  │
├──────────────────────────────────────────────┤
│  Si coincide: req.user = {                   │
│    userId: 'apiKeyUser',                     │
│    username: 'apiKeyUser',                   │
│    role: 'system',                           │
│    isSystemJob: true                         │
│  }                                           │
└──────────────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────┐
│  Controlador: getHealthStatus()              │
│  - Valida socket conectado                   │
│  - Valida webhooks operacionales             │
│  - Retorna JSON con estado                   │
└──────────────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────┐
│  Respuesta HTTP:                             │
│  - 200: Todo OK + webhooksOperational=true   │
│  - 503: Algo falló + detalles               │
└──────────────────────────────────────────────┘
```

---

## Última Actualización
- **Fecha**: 2026-03-23
- **Estado**: ✅ Implementado y documentado
- **Verificación**: Usar `test_health_endpoint.php` para validar
