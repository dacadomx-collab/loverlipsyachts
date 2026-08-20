# 🤝 03 — API CONTRACTS AND ROUTING

> **Fuente de verdad única** para todos los contratos de API, endpoints registrados y lógica de negocio de las rutas.
> **Mandamiento #5:** Prohibido alterar nombres de propiedades JSON definidos aquí sin autorización del Arquitecto.
> **Mandamiento #14:** Todo endpoint que modifique datos requiere autenticación real (Mandamiento 14 acepta explícitamente "JWT o Tokens de sesión" — este proyecto usa la segunda opción, ver abajo).

> **⚠️ Nota de corrección (2026-08-20):** este documento nació como plantilla genérica (`[PLACEHOLDER]`) y describía un patrón de `Authorization: Bearer <JWT_TOKEN>` + `api/cors.php`/`api/jwt.php`/`api/auth_middleware.php` que **nunca existió en este proyecto real** — ninguno de esos tres archivos existe en el repo. El patrón de seguridad documentado abajo (sección "PATRONES DE SEGURIDAD") fue corregido para reflejar lo que el código real efectivamente implementa en cada endpoint (verificado directamente en `api/leads.php`, `api/bookings.php`, `core/auth_check.php`), no una aspiración sin construir. El resto de la plantilla (protocolo de integración, códigos HTTP, blindaje técnico base) sí aplica y no se ha corregido porque coincide con lo real.

---

## 📡 PROTOCOLO DE INTEGRACIÓN (ESTÁNDAR GLOBAL)

| Parámetro | Valor |
| :--- | :--- |
| **Formato** | JSON UTF-8 |
| **Charset** | `Content-Type: application/json; charset=utf-8` |
| **CORS** | No hay archivo `cors.php` — este proyecto no sirve su API a terceros orígenes; todas las llamadas AJAX son mismo-origen desde el propio cockpit. CORS no reemplaza auth de todas formas. |
| **Autenticación** | Sesión de servidor PHP (`session_start()` + `session_regenerate_id()` en `core/auth_check.php`) + token CSRF por sesión (`hash_equals()`), enviado como campo `csrf_token` en el body POST — no como header `Authorization`. |
| **Métodos** | Casi todos los endpoints reales de este proyecto son **POST-only con una acción en el body** (`action=list\|detail\|...`), no rutas GET/PUT/DELETE separadas por recurso — ver ejemplos reales abajo. `api/public/l.php` es la excepción pública (GET, sin auth, ver su registro abajo). |

### Estructura Standard de Respuesta (INAMOVIBLE)

```json
{
  "status":  "success | error",
  "message": "Descripción legible del resultado",
  "data":    [] 
}
```

- `status`: Siempre `"success"` o `"error"`. Sin variantes.
- `message`: String en español, legible por el usuario o el frontend.
- `data`: Array de objetos o array vacío `[]`. **NUNCA** `null`.

### Códigos HTTP Estándar del Sistema

| Código | Uso |
| :--- | :--- |
| `200` | OK — operación exitosa con datos |
| `201` | Created — recurso creado exitosamente |
| `204` | No Content — preflight OPTIONS |
| `400` | Bad Request — payload JSON malformado |
| `401` | Unauthorized — sin token o token inválido/expirado |
| `403` | Forbidden — token válido pero sin permisos (rol insuficiente / CORS) |
| `404` | Not Found — recurso no encontrado |
| `405` | Method Not Allowed — método HTTP incorrecto |
| `409` | Conflict — conflicto de estado (ej: cancelar algo ya cancelado) |
| `422` | Unprocessable Entity — payload válido pero datos inválidos (validación) |
| `500` | Internal Server Error — error de servidor (nunca exponer detalles al frontend) |

---

## 🛡️ ENFORCEMENT AUTOMÁTICO DE CONTRATOS

- **Cero Deriva (JSON Schema):** Por cada endpoint documentado aquí, la IA Ejecutora (Claude) DEBE crear validaciones estrictas en PHP para que la API rechace cargas inválidas con un `422` antes de tocar la base de datos.
- **Librería de Snippets:** Para componentes repetitivos, consultar primero `/knowledge/snippets/`. No reinventar si ya existe un componente blindado.
- **Prepared Statements:** Toda consulta SQL usa `PDO::prepare()` + `execute([])`. Sin excepción.

---

## 🛠️ ENDPOINTS REGISTRADOS

### Plantilla de Registro de Endpoint

```
### Endpoint: `api/[nombre_archivo.php]`
- **Método:**        GET | POST | PUT | DELETE
- **Auth:**          Público | Bearer JWT | Bearer JWT + Role: [rol]
- **Descripción:**   [Qué hace este endpoint en lenguaje natural]
- **Payload (Front → Back):**
  ```json
  { "campo": "tipo | descripción" }
  ```
- **Response (Back → Front):**
  ```json
  { "status": "success", "message": "...", "data": [ { "campo": "tipo" } ] }
  ```
- **Errores Posibles:** 401 (sin auth), 422 (payload inválido), 500 (error interno)
```

---

### Endpoint: `api/[nombre_archivo.php]`
- **Método:** [GET / POST]
- **Auth:** [Público / Sesión de servidor]
- **Descripción:** [Descripción de la acción]
- **Payload Requerido (Front):**
```json
{ "propiedad": "tipo" }
```
- **Response Expected (Back):**
```json
{ "status": "success", "message": "string", "data": [] }
```

---

## 🛠️ ENDPOINTS REALES REGISTRADOS (verificado contra código, no aspiracional)

> **Alcance de este registro (2026-08-20):** los 4 endpoints de abajo son los que se tocaron/verificaron directamente en los hitos recientes de este proyecto (Leads CRM, Agenda, Cotizaciones/Invitación). **No es un inventario exhaustivo** — este proyecto tiene ~20 endpoints reales más en `api/` (`fleet_catalog.php`, `pgai_settings.php`, `prompt_editor.php`, `notification_templates.php`, `module_doc_editor.php`, `aura_diagnostic.php`, `openai_diagnostic.php`, `test_connection.php`, `translate.php`, `login.php`, `book_editor.php`, `ephemeral_links.php`, `public/ai_widget_gateway.php`, `public/whatsapp_webhook.php`, etc.) que aún no tienen su ficha aquí. Mandamiento 4 (Protocolo Anti-Alucinación) sigue aplicando: cualquier endpoint que la IA Ejecutora necesite y no esté todavía en esta lista debe leerse directamente del código fuente antes de asumir su contrato, no inventarse.

### Endpoint: `api/leads.php`
- **Método:** POST (acción en el body, no rutas separadas)
- **Auth:** Sesión de servidor (`lly_is_authenticated()`) + CSRF de body — nunca rota el token en `list`/`detail` (ambas son de solo lectura)
- **Descripción:** Leads CRM unificado (WhatsApp + Widget Web) para `leads.php`. `list` trae hasta 100 sesiones recientes con filtro opcional de fecha; `detail` trae el resumen ejecutivo + transcripción completa de una sesión.
- **Payload (Front → Back):**
  ```json
  { "action": "list", "csrf_token": "string", "date_from": "YYYY-MM-DD | omitido", "date_to": "YYYY-MM-DD | omitido" }
  ```
  ```json
  { "action": "detail", "csrf_token": "string", "session_id": "int (omnichannel_sessions.id, no el UUID público)" }
  ```
- **Response (Back → Front):**
  ```json
  { "status": "success", "leads": [ { "id": 1, "session_uuid": "...", "lead_name": "...", "lead_phone": "...", "lead_email": "...", "lead_date": "...", "lead_pax": 4, "lead_route": "...", "summary": "...", "display_name": "...", "is_vip": 0, "channel_type": "web|whatsapp" } ], "csrf_token": "string" }
  ```
- **Errores Posibles:** 401 (sin sesión), 403 (CSRF inválido), 405 (método), 400 (`session_id` faltante/acción desconocida), 404 (`detail` sin resultado), 503 (BD no disponible — degrada, nunca 500 crudo)

### Endpoint: `api/bookings.php`
- **Método:** POST (acción en el body)
- **Auth:** Sesión de servidor + CSRF de body — mismo patrón que `leads.php`
- **Descripción:** Calendario híbrido de dos niveles para `agenda.php`. `list` mezcla `yacht_bookings` (🟢 confirmadas) con leads de `omnichannel_sessions` sin formalizar (🟡, excluidos automáticamente si ya tienen reserva vinculada). `detail` trae un registro por `id` (reserva real) o por `lead_session_id` (lead sin formalizar).
- **Payload (Front → Back):**
  ```json
  { "action": "list", "csrf_token": "string", "year": "int", "month": "int (1-12)" }
  ```
  ```json
  { "action": "detail", "csrf_token": "string", "id": "int (opcional)", "lead_session_id": "int (opcional, uno de los dos requerido)" }
  ```
- **Response (Back → Front):** `{ "status": "success", "bookings": [...], "csrf_token": "string" }` — mismo criterio de "documentado tal cual existe" que `leads.php`.
- **Errores Posibles:** 401, 403, 405, 400.

### Endpoint: `api/public/l.php`
- **Método:** GET
- **Auth:** **Público, sin sesión** — es el único endpoint de este documento pensado para abrirse fuera del cockpit (enlaces compartidos por WhatsApp/email a un cliente final).
- **Descripción:** Redención de enlaces efímeros de cotización (`?t=token`, autodestructivo por conteo de vistas) y modo de vista previa estática sin expiración (`?sample=balandra|espiritu_santo`, sin BD/token, usado por `ai-showcase.php` e `invitation.php` para demos estables). Ver `modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md` sección 8.8 para el patrón agnóstico completo.
- **Payload:** Query string únicamente (`t` o `sample`), sin body.
- **Response:** HTML completo server-rendered (página de cotización), no JSON — es la única excepción de contrato de este documento porque su consumidor es un navegador humano vía enlace compartido, no un `fetch()` del propio cockpit.
- **Errores Posibles:** token real inválido/expirado → página de cortesía neutra (nunca error técnico crudo, ver Mandamiento 2). `sample` con slug desconocido → degrada a `balandra` (nunca 404).

### Endpoint: `invitation.php` / `my-book/invitation.php`
- **Método:** GET
- **Auth:** Público, sin sesión (misma familia que `book.php`/`my-book/index.php`).
- **Descripción:** No es un endpoint de datos — es una página estática server-rendered (sin `Conexion`/DB) para la invitación VIP al evento de lanzamiento de *Nine Lives. One True Love*. Se registra aquí solo por completitud de rutas públicas, no porque tenga un contrato JSON.
- **Payload / Response:** N/A — HTML puro.

---

## 🧠 LÓGICA DE NEGOCIO (REGLAS DE PIEDRA)

Las reglas de negocio son invariantes del sistema. La IA Ejecutora implementa la lógica; no la inventa ni la modifica sin autorización.

1. **Extracción de leads siempre determinística, nunca delegada al LLM:** nombre/teléfono/correo/fecha/pax/ruta se extraen por regex + plantilla en `core/PgAiActionProcessor.php`, no pidiéndole al modelo que emita campos estructurados. Verificado en producción (`docs/02_SYSTEM_CODEX_REGISTRY.md`, hito de captura determinística) — el motor central no era confiable emitiendo formato consistente.
2. **Modelo de sociedad 50% Cash / 50% Trade Credits:** todo pago de reporte/desarrollo ya ejecutado en `alianzas.php` se divide bajo este esquema fijo — no es negociable por endpoint, es una regla de negocio transversal del proyecto (ver `docs/00_ADN_DEL_PROYECTO.md` sección 5).
3. **Blindaje Técnico Base:**
   - `TRIM()` en todos los strings de entrada antes de persistir
   - `CAST` / tipado fuerte en todos los campos numéricos (`(int)`, `(float)`)
   - Validar contra `NaN`, `null`, strings vacíos en campos requeridos
   - Longitud máxima validada antes de tocar la DB (coincide con longitud de columna)

---

## 📐 PATRONES DE SEGURIDAD EN ENDPOINTS (REAL — verificado en `api/leads.php`/`api/bookings.php`, 2026-08-20)

Todo endpoint privado (owner-only) de este proyecto sigue este patrón real de 5 capas — no el patrón JWT/CORS genérico que este documento tenía antes de la corrección de arriba:

```php
<?php
declare(strict_types=1);

// 1. CONEXIÓN + AUTH DE SESIÓN (ambos por require, nunca require_once duplicado
//    entre sí — ver el bug real de clase duplicada documentado en el Codex)
require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

function lly_endpoint_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. AUTH — sesión de servidor, no JWT
if (!lly_is_authenticated()) {
    lly_endpoint_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

// 3. MÉTODO HTTP — casi todos los endpoints reales son POST-only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_endpoint_json('error', ['message' => 'Method not allowed.'], 405);
}

// 4. CSRF — campo de body, no header. Acciones de solo lectura NUNCA rotan
//    el token (ver nota de condición de carrera en api/leads.php) — solo
//    las que mutan datos rotan.
$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_endpoint_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}

// 5. LÓGICA DE NEGOCIO — action-based dispatch, prepared statements, try/catch
$pdo = Conexion::getConnection();
$action = (string) ($_POST['action'] ?? '');
switch ($action) {
    case 'list':
        // ... $stmt = $pdo->prepare(...); $stmt->execute([...]);
        lly_endpoint_json('success', ['data' => [] /* ... */]);
    default:
        lly_endpoint_json('error', ['message' => 'Unknown action.'], 400);
}
```

**Nota honesta de contrato:** el contrato "INAMOVIBLE" `{status, message, data}` declarado arriba en este documento **no** se cumple al pie de la letra en el código real — los endpoints reales devuelven `['status' => ..., ...] + $extra` con claves que varían por endpoint (`leads`/`bookings`/`csrf_token`/etc.), no siempre una clave `data` fija. Se documenta así, no como debiera ser, siguiendo el mismo criterio de honestidad que `docs/02_DATABASE_SCHEMA_BLUEPRINT.md` ya aplica a sus tablas reales. Homologar todos los endpoints al contrato estricto `{status, message, data}` es un cambio de código real (no solo de documentación) que no se ha hecho — no se emprendió en este hito porque no fue lo pedido y tocaría ~15 endpoints reales en producción sin necesidad funcional actual.
