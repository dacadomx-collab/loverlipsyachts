---
modulo: MOD_CONEXION_SATELLITE_AURA_M2M
nombre: Conexión Satélite M2M a Motor Central AURA
tipo: Blueprint de Construcción — Checklist Táctico
alcance: Genérico / Agnóstico de Stack Cliente (WordPress, HTML puro, React, o cualquier lenguaje backend con soporte cURL/HTTP)
nucleo_inferencia: Servidor Linux AURA (motor de inferencia centralizado, fuera del árbol del proyecto cliente)
clasificacion: Molde Reutilizable — Santuario_Genesis
version: 1.4
fecha: 2026-08-03
autoridad: Arquitecto (DCD LABS / ACADEP)
tono: Calma Ejecutiva (Executive Calm)
---

# MOD_CONEXION_SATELLITE_AURA_M2M

> Este documento es la fuente de verdad para conectar, desde cero, cualquier proyecto clonado del Santuario_Genesis a un motor de inferencia central **AURA** vía un canal machine-to-machine (M2M) autenticado por llave estática. No es un chatbot ni un framework de conversación — es el **satélite de conexión**: un cliente HTTP delgado, sin lógica de negocio, que garantiza que el proyecto cliente jamás vea ni almacene una llave de proveedor LLM, solo la llave de túnel M2M hacia AURA.

Este molde es complementario a `MOD_OPERADOR_COGNITIVO_OMNICANAL.md` (que cubre el aislamiento HMAC por tenant vía `ProxyBridge`). Un mismo proyecto puede tener ambos caminos de despacho provisionados en paralelo — uno no reemplaza al otro salvo decisión explícita del Arquitecto — pero solo debe existir **un** camino activo a la vez consumiendo tráfico real de visitantes.

---

## 1. Filosofía y Arquitectura M2M

### 1.1 Principio rector: Aislamiento Total de Llaves LLM

El servidor Linux AURA es el único componente que conoce las llaves reales de los proveedores de LLM (OpenAI/Claude/Gemini/equivalente). El proyecto cliente nunca las ve, nunca las almacena, y nunca las transmite. Lo único que el proyecto cliente posee es una **llave de túnel M2M** (`{{API_KEY}}`) — un secreto compartido que autentica al proyecto frente a AURA, no un secreto que autentica frente al proveedor del LLM.

### 1.2 Comunicación servidor a servidor, nunca navegador a AURA

El navegador del visitante final **nunca** llama directamente a AURA. Siempre habla con un endpoint propio del servidor del cliente (el "satélite"), y es ese servidor — no el navegador — quien firma y reenvía la petición vía `X-AURA-KEY`. Esto evita que la llave M2M quede expuesta en el código fuente servido al cliente (DevTools, `view-source`, requests de red interceptables).

### 1.3 Matriz de Aislamiento

| Componente | Ubicación Física | Responsabilidad |
|---|---|---|
| Frontend / UI de prueba | Navegador del operador o del visitante final | Captura el prompt, muestra la respuesta. Nunca ve `{{API_KEY}}`. |
| Satélite de Conexión (PHP, ligero) | Servidor del Cliente | Inyecta `X-AURA-KEY`, aplica el protocolo de fallback LAN→WAN, dispara la llamada saliente, mapea la respuesta. |
| Motor Central AURA | Servidor Linux (núcleo, fuera del árbol del cliente) | Valida la llave, ejecuta la inferencia, factura tokens, retorna JSON estandarizado. |

El satélite del cliente **no razona**: solo empaqueta, autentica y reenvía. Toda decisión cognitiva (motor, modelo, prompt de sistema) vive del lado de AURA.

### 1.4 Protocolo de Contexto Persistente M2M (v1.4)

Hasta la v1.3 de este molde, cada despacho de chat reenviaba el prompt de sistema completo del tenant (persona, reglas de negocio, catálogo de hechos verificados) concatenado al mensaje del huésped — en un proyecto real esto llegó a pesar ~10 KB por mensaje y provocó timeouts/`502` del lado del motor central ante prompts grandes en instancias con recursos limitados (ver el Codex de ese proyecto para el hallazgo original). El Protocolo de Contexto Persistente reduce esto a tres reglas de oro:

1. **AURA Linux es un Agente Autónomo.** No es un simple proxy de inferencia — ella misma orquesta y decide qué motor/modelo del servidor Linux procesa cada solicitud según el `agent_id` recibido. El satélite cliente no elige motor ni modelo; solo identifica el agente.
2. **Onboarding de Contexto Persistente.** El System Prompt y la personalidad de un proyecto se registran **una sola vez** en el servidor Linux (no en cada mensaje) — ver `syncTenantContext()` en la sección 5.5 para el artefacto de referencia de este registro desde el lado del satélite, y la nota de esa misma sección sobre el estado real de ese contrato (provisional hasta confirmación de AURA).
3. **Payload de Chat Ultra-Liviano.** Las llamadas recurrentes del widget de chat (sección 3.1) envían únicamente `{ agent_id, user_session, prompt }` donde `prompt` es el mensaje crudo del huésped — sin el System Prompt reanexado. Orden de magnitud esperado: ~200 bytes por mensaje, no ~10 KB.

**Consecuencia arquitectónica:** el satélite cliente deja de ser responsable de "recordarle" a AURA quién es el tenant en cada mensaje — esa responsabilidad se mueve enteramente al onboarding de la regla 2. Un proyecto que siembra este molde sin haber completado el onboarding recibirá respuestas genéricas/sin personalidad de AURA (el motor sigue funcionando, solo sin contexto de tenant) — esto es una señal de "onboarding pendiente", no un fallo de conexión.

---

## 2. Matriz de Red y Fallback Dinámico

AURA se expone en dos rutas de red independientes hacia el mismo motor central:

| Ruta | Host típico | Cuándo se usa | Timeout de conexión |
|---|---|---|---|
| **LAN (primaria)** | `{{LAN_BASE_URL}}` (ej. `http://192.168.1.224:8080`) | El servidor del cliente vive en la misma red local/VPN que AURA — ruta más rápida, sin salir a Internet. | ≤ 3s |
| **WAN (fallback)** | `{{WAN_FALLBACK_URL}}` (ej. `https://aura.acadep.com`) | La ruta LAN no responde dentro del timeout, o el servidor del cliente no comparte red local con AURA (hosting compartido remoto). | ≤ 8s (timeout de lectura completo) |

**Protocolo de conmutación (obligatorio, no opcional):**

1. Intentar siempre la ruta LAN primero si `{{LAN_BASE_URL}}` está configurada — es la ruta de menor latencia.
2. Si la conexión LAN falla por **timeout de conexión** (no de lectura — un timeout de lectura ya en curso significa que el servidor sí respondió y no debe reintentarse a ciegas, ver 5.3), por error de resolución DNS/red, o por un código de estado `5xx`/`504`, conmutar automáticamente a la ruta WAN sin exponer el fallo intermedio al llamador.
3. Registrar en telemetría **qué canal respondió** (`lan` | `wan`) — es una métrica operativa, no solo de depuración: revela silenciosamente si la red local se degradó.
4. Si ambas rutas fallan, degradar de forma controlada (ver Fase 2 del checklist) — nunca un error 500 crudo hacia el llamador final.
5. No cachear "la LAN está caída" de forma permanente en memoria del proceso — cada petición reintenta LAN primero. Si el volumen de tráfico lo justifica, un circuito breaker con TTL corto (segundos, no minutos) es una mejora aceptable, no un requisito de este molde base.

### 2.1 Selección de Puerto — Desambiguación Multi-Tenant

Un mismo host LAN puede exponer **más de una instancia** del motor AURA en puertos distintos — cada puerto es una instancia lógicamente independiente, con su propia tabla de llaves y su propia cuota, aunque comparta el mismo hardware físico. `{{ENV_PREFIX}}_BASE_URL` debe especificar el puerto explícitamente; nunca asumir un puerto por defecto en el código del cliente.

| Puerto | Endpoint | Instancia | Base de datos de tokens | Uso típico |
|---|---|---|---|---|
| `:8080` | `/api/v2/aura/gateway` | **Core Principal** (AURA General) | `aura_db` | Motor de inferencia genérico, multi-proyecto. |
| `:8090` | `/api/v2/aura/gateway` | **Satélite** (instancia dedicada/aislada) | `axon_core_db` | Instancia dedicada a un tenant o línea de negocio específica — aislamiento de cuota y de llaves respecto al Core Principal. |

- El path del endpoint (`/api/v2/aura/gateway`) es el mismo en ambas instancias — lo que las distingue es exclusivamente el puerto en `{{ENV_PREFIX}}_BASE_URL`.
- Cada instancia valida las llaves M2M contra su propia base (`aura_db` para `:8080`, `axon_core_db` para `:8090`, ver también `satellite_tokens` en 4.1) — una llave válida en una **no** es válida automáticamente en la otra. Un `401` puede significar simplemente "llave correcta, puerto/base equivocada" — verificar el puerto configurado antes de asumir que la llave está mal generada.
- Si un proyecto necesita hablar con ambas instancias (ej. un agente genérico en `:8080` y un agente dedicado en `:8090`), instanciar dos `AuraSatelliteClient` independientes, cada uno con su propio `{{ENV_PREFIX}}_BASE_URL`/`{{ENV_PREFIX}}_KEY` — nunca compartir una sola instancia del cliente entre ambos puertos alternando configuración en tiempo de ejecución.

### 2.2 Resiliencia de Red — Fallback por IP Directa

Un tercer escalón, más allá de LAN→WAN (sección 2), cubre un modo de fallo específico: el hostname WAN (`{{ENV_PREFIX}}_FALLBACK_URL`) deja de resolver por DNS (`curl` errno `6` / `CURLE_COULDNT_RESOLVE_HOST`) aunque el servidor siga arriba — un DNS caído, mal configurado, o un registro expirado no significa que AURA esté inalcanzable.

- Variable opcional adicional: `{{ENV_PREFIX}}_FALLBACK_IP` — la IP pública del mismo servidor que resuelve `{{ENV_PREFIX}}_FALLBACK_URL`. Opcional y vacía por defecto; el cliente solo la usa si está configurada.
- **Activación:** únicamente cuando el intento WAN falla específicamente por resolución DNS (errno `6`) — no ante cualquier fallo WAN. Un `401`/`403`/`500` del WAN significa que sí se alcanzó el servidor; reintentar por IP no cambiaría el resultado y solo añadiría latencia.
- **Técnica recomendada (cURL nativo):** forzar la resolución del hostname original a la IP conocida vía `CURLOPT_RESOLVE` (`"{{HOSTNAME}}:{{PORT}}:{{ENV_PREFIX}}_FALLBACK_IP"`), **no** cambiar la URL de destino a la IP cruda. Esto preserva el `Host`/SNI de TLS que el certificado del servidor espera — conectar directo a `https://{{IP}}/...` con un certificado emitido para el hostname produce un fallo de validación de certificado, un problema distinto y peor que el DNS que se intentaba evitar.
- Si `{{ENV_PREFIX}}_FALLBACK_IP` no está configurada, o el intento por IP directa también falla, degradar de forma controlada (ver Fase 2) y reportar en telemetría/diagnóstico que los tres escalones (LAN, WAN por dominio, WAN por IP) fueron intentados — nunca fallar en silencio sin dejar rastro de cuál escalón fue el último probado.

---

## 3. Contrato de Petición y Respuesta Estándar (OCMC-compatible)

### 3.1 Request Body

```json
{
  "agent_id": "{{AGENT_ID}}",
  "user_session": "{{SESSION_ID}}",
  "prompt": "{{USER_PROMPT_TEXT}}"
}
```

- `agent_id` — identifica qué agente/personalidad del lado de AURA debe atender la petición (un proyecto puede tener más de uno: ventas, soporte, diagnóstico).
- `user_session` — identificador de sesión de conversación, opaco para AURA; permite continuidad de contexto si el motor central lo soporta.
- `prompt` — texto ya normalizado/saneado del lado del cliente (nunca HTML crudo, nunca payload de otro canal sin normalizar).

### 3.2 Headers Obligatorios

```
Content-Type: application/json
X-AURA-KEY: {{API_KEY}}
```

`{{API_KEY}}` es el secreto de túnel M2M — no una llave de proveedor LLM (ver 1.1). Rotable independientemente de las llaves maestras del motor central.

#### 3.2.1 Matriz de Equivalencia de Cabeceras

El Gateway AURA acepta la llave M2M por cualquiera de estas dos variantes — son equivalentes, no dos mecanismos distintos:

| Cabecera | Formato | Notas |
|---|---|---|
| `X-AURA-KEY` | `X-AURA-KEY: {{API_KEY}}` | **Estándar recomendado** para este molde — nombre explícito, sin ambigüedad con otros esquemas de auth que el mismo servidor pueda exponer. |
| `Authorization` | `Authorization: Bearer {{API_KEY}}` | Variante compatible con clientes/librerías HTTP genéricas que ya asumen el esquema `Bearer` estándar (ej. algunos SDKs o herramientas de prueba como Postman/Insomnia con auth preconfigurada). |

- Un satélite cliente solo necesita enviar **una** de las dos — enviar ambas no es necesario ni perjudicial, pero añade payload sin beneficio.
- Si un handshake de diagnóstico falla con `401` usando una variante, probar la otra antes de asumir que la llave es inválida — la causa puede ser un middleware intermedio (proxy, WAF) que descarta cabeceras no estándar como `X-AURA-KEY` mientras preserva `Authorization`.
- El artefacto de referencia (sección 5.4) envía `X-AURA-KEY` por ser el estándar recomendado; un satélite cliente puede añadir `Authorization: Bearer` como cabecera adicional si su entorno de red lo requiere, sin cambiar el resto del contrato.

### 3.3 Response Body

```json
{
  "status": "success | error",
  "response": "{{AI_REPLY_TEXT}}",
  "engine": "{{ENGINE_NAME}}",
  "model": "{{MODEL_NAME}}",
  "latencyMs": 0,
  "tokensUsed": 0,
  "tokensRemaining": 0,
  "sessionId": "{{SESSION_ID}}",
  "tenantName": "{{TENANT_ID}}"
}
```

- `latencyMs` — reportado por AURA (tiempo interno de inferencia), **distinto** de la latencia de red medida por el satélite (ver 5.4) — un panel de diagnóstico debe mostrar ambas por separado, nunca fundirlas en un solo número.
- `tokensRemaining` — saldo de cuota del tenant, si el motor central factura por cuota; `null`/ausente si el proyecto no tiene límite configurado.
- `tenantName` — eco de confirmación de qué tenant autenticó la petición — un valor inesperado aquí (distinto al `{{TENANT_ID}}` propio) es una señal de mala configuración que el satélite debe superficie, no silenciar.

> **Nota de compatibilidad (confirmada en validación real, 2026-07-31):** al menos una instancia AURA real observada envuelve este payload bajo una clave `"data"` (`{"status":"success","message":"...","data":{"response":...,"engine":...,...}}`) en lugar de devolverlo plano en la raíz. El satélite cliente debe aceptar **ambas** formas — preferir `data.*` cuando la clave existe y sea un objeto, y solo entonces caer a la raíz — en vez de asumir el contrato plano de forma rígida. Ver 5.4 para la implementación de referencia que ya hace esta normalización.

### 3.4 Onboarding de Contexto (Sincronización de System Prompt) — Contrato Provisional

Regla de oro 2 de la sección 1.4 exige un registro único del System Prompt del lado de AURA, pero **al momento de escribir esta versión (v1.4) AURA no ha publicado un endpoint/contrato oficial de onboarding** — este molde documenta la forma provisional que un satélite cliente puede intentar, sin garantía de aceptación:

```json
{
  "action": "sync_context",
  "agent_id": "{{AGENT_ID}}",
  "tenant": "{{TENANT_ID}}",
  "system_prompt": "{{FULL_SYSTEM_PROMPT_TEXT}}"
}
```

Enviado al mismo `{{BASE_URL}}{{GATEWAY_ENDPOINT}}` que el despacho de chat (sección 3.1), distinguido por el campo `action` — mismo patrón que otros endpoints de una sola ruta con múltiples acciones que ya usa el proyecto concreto que sembró este molde.

> **Hallazgo de validación en vivo (2026-08-03):** un proyecto concreto probó este contrato provisional contra un servidor AURA real (`axon.acadep.com`) y recibió `HTTP 400` — `"Payload inválido: agent_id, user_session y prompt son requeridos."` El servidor real valida estrictamente el mismo esquema que el despacho de chat (sección 3.1) y **no reconoce** los campos `action`/`tenant`/`system_prompt` de este contrato provisional. Dos lecturas posibles, ninguna confirmada aún: (a) el onboarding de la regla de oro 2 ocurre por un mecanismo distinto a una llamada API desde el satélite (ej. configuración manual del lado del administrador de AURA), o (b) existe un endpoint/contrato de onboarding real que todavía no se ha publicado/confirmado a los proyectos cliente. **No usar este contrato en producción sin una confirmación explícita de AURA** — el artefacto de referencia (`syncTenantContext()`, sección 5.5) implementa esta forma provisional exactamente para poder validarla en el momento en que AURA confirme o corrija el contrato real, no como una integración ya garantizada.

---

## 4. Checklist Táctico de Conexión

> Regla de avance: ninguna fase se marca cerrada sin verificación funcional contra el servidor AURA real (no solo contra un mock). No saltar fases.

### Fase 0 — Variables de Entorno

- [ ] `{{ENV_PREFIX}}_BASE_URL` — host LAN primario (ej. `http://192.168.1.224:8080`).
- [ ] `{{ENV_PREFIX}}_GATEWAY_ENDPOINT` — path del endpoint (ej. `/api/v2/aura/gateway`), separado del host para poder rotar dominio sin tocar el path.
- [ ] `{{ENV_PREFIX}}_KEY` — llave de túnel M2M. Nunca hardcodeada en código fuente, nunca commiteada, nunca expuesta a una respuesta JSON hacia el navegador.
- [ ] `{{ENV_PREFIX}}_TENANT` — identificador del tenant, coincide con `tenantName` esperado en la respuesta (ver 3.3).
- [ ] `{{ENV_PREFIX}}_FALLBACK_URL` — host WAN completo (ej. `https://aura.acadep.com`), independiente del path del endpoint si el WAN usa el mismo `{{ENV_PREFIX}}_GATEWAY_ENDPOINT`.
- [ ] `{{ENV_PREFIX}}_FALLBACK_IP` — opcional (ver 2.2). IP pública del mismo servidor que `{{ENV_PREFIX}}_FALLBACK_URL`, usada solo si el WAN falla por resolución DNS. Vacía por defecto — el proyecto opera con solo dos escalones (LAN/WAN) hasta que se provisione.
- [ ] Confirmar que ninguna de estas variables tiene un valor por defecto hardcodeado en el código del molde — todas deben venir de `.env` o equivalente del stack destino.

### Fase 1 — Cliente HTTP Nativo con Fallback

- [ ] Implementar el cliente usando solo la librería HTTP nativa del stack (cURL en PHP, `fetch`/`http` en Node, `requests`/`httpx` en Python) — cero dependencias externas para un componente tan crítico y de tan bajo nivel.
- [ ] Timeout de **conexión** LAN estricto (≤ 3s) — distinto del timeout de **lectura** total (≤ 8s), aplicado a ambas rutas.
- [ ] Fallback automático LAN → WAN según el protocolo de la sección 2 — transparente para el llamador, que solo ve un resultado final.
- [ ] El cliente expone qué canal respondió (`lan`/`wan`) como parte de su resultado interno, no solo en logs — un panel de diagnóstico lo necesita en tiempo real.

### Fase 2 — Manejo de Excepciones y Mapeo de Códigos HTTP

- [ ] `200` — éxito, mapear `response`/`engine`/`model`/`tokensUsed`/`tokensRemaining` tal cual.
- [ ] `401` / `403` — fallo de autenticación/autorización: la llave es inválida, expiró, o el tenant no coincide. Nunca reintentar automáticamente con la misma llave — superficie el error de configuración, no lo enmascare como "servidor caído".
- [ ] `500` — error interno del motor central. Reintentar una vez contra el mismo canal es aceptable; si persiste, degradar.
- [ ] `504` (o timeout de lectura agotado) — el motor central tardó demasiado en responder. Tratar como fallo de ese canal específico, no como "AURA está caído" — el otro canal (LAN/WAN) puede seguir sano.
- [ ] Cualquier otro código o cuerpo no-JSON — tratar como error genérico, nunca intentar `json_decode` sin verificar antes que el cuerpo es JSON válido.
- [ ] Ninguna excepción de red o de parseo debe propagarse como error 500 crudo hacia el visitante final si este cliente corre detrás de un endpoint público — degradar a una respuesta controlada (mismo patrón "degraded_fallback" de `MOD_OPERADOR_COGNITIVO_OMNICANAL.md`).

### Fase 3 — Telemetría y Registro de Consumo

- [ ] Registrar, por cada petición: timestamp, canal usado (`lan`/`wan`), código HTTP, latencia de red medida por el cliente (wall-clock, ver 5.4), `latencyMs` reportado por AURA, `tokensUsed`, `tokensRemaining`, y si la petición tuvo éxito o no.
- [ ] Nunca registrar el prompt completo del usuario ni la respuesta completa de la IA en logs de texto plano persistentes salvo que el proyecto tenga un requisito explícito de auditoría — preferir un hash o los primeros N caracteres.
- [ ] Nunca registrar `{{API_KEY}}` en ningún log, ni siquiera parcialmente enmascarada junto a suficiente contexto como para ser reconstruible.
- [ ] Si el proyecto ya tiene una tabla de telemetría omnicanal (ver Fase 2 de `MOD_OPERADOR_COGNITIVO_OMNICANAL.md`), reutilizarla con un `channel_type` o campo equivalente distinguiendo el origen `aura_m2m` — no crear una tabla paralela sin necesidad real.

### Fase 4 — Validación en Vivo (Diagnóstico)

- [ ] Construir (o reutilizar) una vista de diagnóstico protegida por autenticación de propietario/administrador — nunca pública — que ejecute un handshake real contra AURA y muestre: estado de conexión, canal activo (LAN/WAN), latencia, motor/modelo detectado, y contador de tokens.
- [ ] Incluir un sandbox de prompt de un solo campo para probar la respuesta real de la IA sin tener que desplegar el flujo completo de un canal (WhatsApp/Widget) para validar la conexión.
- [ ] Confirmar que el sandbox nunca expone `{{API_KEY}}` al HTML/JS servido al navegador — el navegador solo habla con el endpoint propio del servidor, que a su vez habla con AURA.
- [ ] Documentar en el Codex del proyecto concreto el resultado de la primera validación en vivo (fecha, canal que respondió, código HTTP) — evidencia de que la Fase 4 se cerró contra el servidor real, no contra una suposición.

### 4.1 Diagnóstico Rápido en 2 Clics (1-Click Test Harness)

Antes de construir la vista de diagnóstico completa (los tres puntos anteriores), un handshake de línea de comandos confirma en segundos si el problema es de red, de credencial, o de cuota — sin depender de que el resto del stack del proyecto ya esté funcionando.

**Plantilla cURL estándar:**

```bash
curl -i -X POST "{{BASE_URL}}{{GATEWAY_ENDPOINT}}" \
  -H "Content-Type: application/json" \
  -H "X-AURA-KEY: {{API_KEY}}" \
  -d '{"agent_id":"diagnostic","user_session":"cli-test","prompt":"ping"}'
```

Sustituir `{{BASE_URL}}` por el host LAN (`{{ENV_PREFIX}}_BASE_URL`, con su puerto explícito — ver 2.1) para la prueba primaria, y por `{{ENV_PREFIX}}_FALLBACK_URL` para confirmar el WAN por separado — cada canal se valida de forma independiente, un `200` en uno no implica nada sobre el otro.

**Tabla de interpretación rápida de códigos HTTP:**

| Código | Significado | Acción |
|---|---|---|
| `200` — OK | Conexión M2M correcta. | Ninguna — capturar la respuesta como evidencia de cierre de Fase 4. |
| `401` — Unauthorized | Llave M2M no registrada o inactiva en la tabla `satellite_tokens` del puerto/instancia asignado (ver 2.1). | Confirmar que la llave corresponde a esa instancia/puerto específico, no a otra; confirmar que no expiró ni fue revocada del lado de AURA. |
| `402` — Limit Exceeded | Cuota de tokens del tenant consumida. | No es un fallo de conexión — la autenticación fue correcta. Requiere ampliar cuota o esperar el ciclo de renovación del lado de AURA; reintentar inmediatamente no resuelve nada. |
| `403` — Forbidden | Llave válida pero sin permiso para el `agent_id` u operación solicitada. | Revisar el alcance/scope asignado a la llave del lado de AURA. |
| `500` — Internal Error | Error interno del motor central. | Aceptable reintentar una vez (ver Fase 2); si persiste, es un problema del lado de AURA, no del satélite. |
| `503` — Busy | Motor en saturación temporal. | Leer la cabecera `Retry-After` de la respuesta (segundos hasta el siguiente intento sugerido) — no reintentar antes de ese plazo; un bucle de reintento inmediato agrava la saturación. |
| `504` / timeout de lectura — Gateway Timeout | El motor tardó demasiado en responder. | Tratar como fallo de ese canal específico (ver 2), no como confirmación de que la llave o la cuota están mal. |

- Este harness es deliberadamente independiente del satélite PHP (sección 5) — sirve para aislar "¿el problema está en mi cliente HTTP o en la configuración/servidor de AURA?" antes de depurar código.
- Un resultado de este harness (código HTTP, canal, timestamp) es evidencia válida para cerrar el punto de documentación de Fase 4 — no hace falta repetir la prueba desde la vista de diagnóstico completa si el harness ya la confirmó.

#### 4.1.1 Procedimiento de Verificación en 4 Niveles

Un `200 OK` en un solo nivel no es evidencia suficiente de que el módulo completo funciona — cada nivel prueba una capa distinta de la cadena, y un fallo silencioso en una capa intermedia (ej. un contrato de respuesta mal parseado) puede pasar desapercibido si solo se valida el nivel 1. Ejecutar los cuatro, en orden, antes de marcar la Fase 4 como cerrada:

| # | Nivel | Qué prueba | Cómo |
|---|---|---|---|
| 1 | **cURL crudo** | Que el servidor AURA responde, en qué puerto/instancia, y con qué forma exacta de payload — sin ninguna capa del proyecto de por medio. | Plantilla de la sección 4.1, ejecutada desde una terminal. |
| 2 | **Ejecutor PHP CLI** | Que `AuraSatelliteClient` (sección 5) parsea correctamente la respuesta real — incluyendo la forma exacta del payload confirmada en el Nivel 1 (plano vs. envuelto en `"data"`, ver 3.3) — sin depender de sesión HTTP, CSRF, ni del navegador. | Un script mínimo que instancia el cliente vía `fromConfig()`/`fromEnv()` y llama `dispatch()` directamente, ejecutado con `php archivo.php` (sin servidor web). |
| 3 | **Endpoint HTTP de diagnóstico** | Que la vista de diagnóstico del proyecto (Fase 4, primeros tres puntos) funciona de punta a punta: sesión de propietario, CSRF, el endpoint propio del servidor, y el cliente M2M — el mismo camino que usaría un operador humano en el navegador. | Petición HTTP real contra `{{DIAGNOSTIC_ENDPOINT_URL}}` con cookie de sesión válida y token CSRF vigente (nunca sin autenticación — ver el punto de "nunca pública" en Fase 4). |
| 4 | **Logs de servidor** | Que la telemetría de la sección Fase 3 quedó registrada correctamente — canal usado, código HTTP, latencias, tokens — y que un fallo (ej. DNS caído) se registró de forma limpia en una sola entrada, sin stack trace ni petición colgada. | Inspección del log de errores del stack destino (ej. `error_log` de PHP/Apache) inmediatamente después de los Niveles 2 y 3. |

- Los cuatro niveles deben ejecutarse contra el servidor AURA real — repetir el Nivel 1 contra un mock y dar por cerrada la Fase 4 viola la regla de avance de la sección 4.
- Si el Nivel 1 tiene éxito pero el Nivel 2 falla (o devuelve campos de telemetría en `null` con `success: true`), la causa más probable es un desajuste de contrato (ver nota de compatibilidad en 3.3) — no un problema de red ni de credencial.
- El Nivel 4 es el que confirma que el módulo es operable en producción, no solo "funciona en el momento de la prueba" — un log limpio ante un fallo real (DNS, timeout) es tan importante de verificar como un log de éxito.

---

## 5. Artefacto de Código de Referencia — `AuraSatelliteClient`

> Molde genérico: sustituir los placeholders al momento de sembrar este archivo en un proyecto clonado — ningún host, tenant ni llave real de un cliente concreto debe vivir en este documento agnóstico (Mandato de Sincronización Génesis, igual que en `MOD_OPERADOR_COGNITIVO_OMNICANAL.md`).

### 5.1 Configuración (constructor)

El cliente recibe su configuración ya resuelta desde `.env` — nunca la lee directamente, para mantenerse testeable y agnóstico del mecanismo de configuración del stack destino.

### 5.2 Firma pública

```
AuraSatelliteClient::__construct(baseUrl, gatewayEndpoint, apiKey, tenant, fallbackUrl, fallbackIp)
AuraSatelliteClient::dispatch(agentId, sessionId, prompt): AuraSatelliteResult
AuraSatelliteClient::syncTenantContext(agentId, systemPrompt): AuraSatelliteResult
```

`AuraSatelliteResult` expone: `success` (bool), `httpCode`, `channelUsed` (`lan`/`wan`/`wan_ip`/`none` — ver 2.2), `networkLatencyMs` (medido por el cliente), `reportedLatencyMs` (campo `latencyMs` del payload de AURA), `response`, `engine`, `model`, `tokensUsed`, `tokensRemaining`, `sessionId`, `tenantName`, `errorMessage`. `fallbackIp` es opcional — si se omite, el cliente opera con solo los dos escalones LAN/WAN (comportamiento idéntico al de v1.0/v1.1).

### 5.5 `syncTenantContext()` — Onboarding Administrativo (v1.4, provisional)

Método de baja frecuencia (llamado manualmente por un operador/administrador cuando el System Prompt del tenant cambia — nunca en el camino de despacho de chat) que envía el contrato provisional de la sección 3.4. Reutiliza internamente la misma cascada LAN→WAN→WAN-por-IP que `dispatch()` (ambos deben delegar a una única implementación interna compartida — nunca duplicar la lógica de fallback entre los dos métodos públicos, ver 5.4). Ver la nota de validación en vivo de la sección 3.4 antes de asumir que este contrato es aceptado por un servidor AURA concreto — confirmarlo primero contra ese servidor.

### 5.3 Nota sobre reintentos

Un timeout de **conexión** (el socket nunca llegó a establecerse) es seguro de reintentar contra el canal alterno — no hay forma de que el servidor haya procesado nada. Un timeout de **lectura** (el socket se conectó, la petición se envió, pero la respuesta nunca llegó) es ambiguo: el servidor pudo haber procesado la petición (y consumido tokens) sin que el cliente lo sepa. El artefacto de referencia solo hace fallback automático ante fallos de conexión — un timeout de lectura se reporta como error del canal que lo sufrió, sin reintento automático silencioso, para no arriesgar una doble ejecución de la misma petición contra el motor central.

### 5.4 Clase PHP

PHP 8.x estricto, cero dependencias externas, cURL nativo.

```php
<?php
declare(strict_types=1);

/**
 * AuraSatelliteClient — Cliente M2M genérico hacia un motor central AURA.
 * Molde agnóstico: sustituir los placeholders de configuración al sembrar
 * este archivo en un proyecto clonado (ver MOD_CONEXION_SATELLITE_AURA_M2M.md).
 * No razona, no decide: solo autentica, despacha, y aplica el protocolo
 * de fallback LAN → WAN de la sección 2.
 */
final class AuraSatelliteClient
{
    private const LAN_CONNECT_TIMEOUT = 3;
    private const READ_TIMEOUT        = 8;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $gatewayEndpoint,
        private readonly string $apiKey,
        private readonly string $tenant,
        private readonly ?string $fallbackUrl = null,
        private readonly ?string $fallbackIp = null,
    ) {
    }

    /** Builds an instance from already-resolved config (e.g. from .env) — never reads .env itself. */
    public static function fromConfig(array $config): self
    {
        return new self(
            baseUrl: (string) ($config['base_url'] ?? ''),
            gatewayEndpoint: (string) ($config['gateway_endpoint'] ?? ''),
            apiKey: (string) ($config['api_key'] ?? ''),
            tenant: (string) ($config['tenant'] ?? ''),
            fallbackUrl: $config['fallback_url'] ?? null,
            fallbackIp: $config['fallback_ip'] ?? null,
        );
    }

    /**
     * Dispatches one prompt. Tries the LAN host first; on a connection
     * failure (not a read timeout — see 5.3) falls back to the WAN host
     * once. If the WAN failure is specifically a DNS resolution failure
     * (section 2.2) and a direct IP is configured, a third attempt pins
     * DNS to that IP via CURLOPT_RESOLVE. Never throws — always returns
     * a result object the caller can render or degrade from.
     */
    public function dispatch(string $agentId, string $sessionId, string $prompt): array
    {
        // Protocolo de Contexto Persistente M2M (sección 1.4, v1.4): el
        // payload de chat es ultra-liviano a propósito — nunca reanexar
        // el System Prompt completo aquí. Ver syncTenantContext() abajo.
        return $this->dispatchPayload([
            'agent_id'     => $agentId,
            'user_session' => $sessionId,
            'prompt'       => $prompt,
        ]);
    }

    /**
     * Onboarding administrativo (sección 3.4/5.5) — llamado manualmente,
     * nunca en el camino de despacho de chat. Contrato provisional, sin
     * confirmación oficial de AURA — ver la nota de validación en vivo
     * de la sección 3.4 antes de asumir que un servidor AURA concreto lo
     * acepta.
     */
    public function syncTenantContext(string $agentId, string $systemPrompt): array
    {
        return $this->dispatchPayload([
            'action'        => 'sync_context',
            'agent_id'      => $agentId,
            'tenant'        => $this->tenant,
            'system_prompt' => $systemPrompt,
        ]);
    }

    /** Cascada compartida LAN → WAN → WAN-por-IP (sección 2) — dispatch() y syncTenantContext() delegan aquí para no duplicar la política de fallback. */
    private function dispatchPayload(array $payload): array
    {
        if ($this->apiKey === '' || $this->baseUrl === '') {
            return $this->result(success: false, channel: 'none', errorMessage: 'AURA client not configured (missing base URL or API key).');
        }

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $lanResult = $this->attempt($this->baseUrl . $this->gatewayEndpoint, $body, 'lan');

        if (!$lanResult['connectFailed'] || $this->fallbackUrl === null || $this->fallbackUrl === '') {
            return $lanResult['raw'];
        }

        $wanUrl    = $this->fallbackUrl . $this->gatewayEndpoint;
        $wanResult = $this->attempt($wanUrl, $body, 'wan', readTimeout: self::READ_TIMEOUT + 4);

        if (!$wanResult['dnsFailed'] || $this->fallbackIp === null || $this->fallbackIp === '') {
            return $wanResult['raw'];
        }

        // Third tier (section 2.2): WAN domain didn't resolve, but a
        // known public IP for that same server is configured.
        $wanIpResult = $this->attempt($wanUrl, $body, 'wan_ip', readTimeout: self::READ_TIMEOUT + 4, resolveIp: $this->fallbackIp);
        return $wanIpResult['raw'];
    }

    /**
     * @return array{connectFailed: bool, dnsFailed: bool, raw: array}
     */
    private function attempt(string $url, string $body, string $channel, ?int $readTimeout = null, ?string $resolveIp = null): array
    {
        $start = microtime(true);

        $ch   = curl_init($url);
        $opts = [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::LAN_CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => $readTimeout ?? self::READ_TIMEOUT,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-AURA-KEY: ' . $this->apiKey,
            ],
        ];

        if ($resolveIp !== null) {
            $parts = parse_url($url);
            $host  = $parts['host'] ?? '';
            $port  = $parts['port'] ?? (($parts['scheme'] ?? 'http') === 'https' ? 443 : 80);
            if ($host !== '') {
                // Pins DNS for this request only — preserves Host/SNI so TLS
                // validates against the hostname's real certificate, unlike
                // connecting to "https://{ip}/..." directly (section 2.2).
                $opts[CURLOPT_RESOLVE] = [$host . ':' . $port . ':' . $resolveIp];
            }
        }

        curl_setopt_array($ch, $opts);

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $networkLatencyMs = (int) round((microtime(true) - $start) * 1000);

        // CURLE_COULDNT_CONNECT (7) / CURLE_COULDNT_RESOLVE_HOST (6) during
        // connect are the only failures safe to retry on the alternate
        // channel — see section 5.3. Everything else (including a read
        // timeout) is final for this attempt. dnsFailed narrows to exactly
        // the DNS case, which is what unlocks the direct-IP third tier.
        $dnsFailed     = $errno === CURLE_COULDNT_RESOLVE_HOST;
        $connectFailed = $dnsFailed || $errno === CURLE_COULDNT_CONNECT;

        if ($errno !== 0) {
            return [
                'connectFailed' => $connectFailed,
                'dnsFailed'     => $dnsFailed,
                'raw' => $this->result(
                    success: false,
                    channel: $channel,
                    networkLatencyMs: $networkLatencyMs,
                    httpCode: 0,
                    errorMessage: 'Network error (curl ' . $errno . ') on ' . $channel . ' channel.',
                ),
            ];
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            return [
                'connectFailed' => false,
                'dnsFailed'     => false,
                'raw' => $this->result(
                    success: false,
                    channel: $channel,
                    networkLatencyMs: $networkLatencyMs,
                    httpCode: $httpCode,
                    errorMessage: 'Non-JSON response from ' . $channel . ' channel (HTTP ' . $httpCode . ').',
                ),
            ];
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return [
                'connectFailed' => false,
                'dnsFailed'     => false,
                'raw' => $this->result(
                    success: false,
                    channel: $channel,
                    networkLatencyMs: $networkLatencyMs,
                    httpCode: $httpCode,
                    errorMessage: (string) ($decoded['message'] ?? 'Unauthorized — check API key / tenant.'),
                ),
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'connectFailed' => false,
                'dnsFailed'     => false,
                'raw' => $this->result(
                    success: false,
                    channel: $channel,
                    networkLatencyMs: $networkLatencyMs,
                    httpCode: $httpCode,
                    errorMessage: (string) ($decoded['message'] ?? ('AURA responded with HTTP ' . $httpCode)),
                ),
            ];
        }

        // Accept both a flat payload and one wrapped under "data" — see the
        // compatibility note in section 3.3. Prefer "data" when present.
        $payload = is_array($decoded['data'] ?? null) ? $decoded['data'] : $decoded;

        return [
            'connectFailed' => false,
            'dnsFailed'     => false,
            'raw' => $this->result(
                success: (($decoded['status'] ?? '') === 'success'),
                channel: $channel,
                networkLatencyMs: $networkLatencyMs,
                httpCode: $httpCode,
                response: $payload['response'] ?? null,
                engine: $payload['engine'] ?? null,
                model: $payload['model'] ?? null,
                reportedLatencyMs: isset($payload['latencyMs']) ? (int) $payload['latencyMs'] : null,
                tokensUsed: isset($payload['tokensUsed']) ? (int) $payload['tokensUsed'] : null,
                tokensRemaining: isset($payload['tokensRemaining']) ? (int) $payload['tokensRemaining'] : null,
                sessionId: $payload['sessionId'] ?? null,
                tenantName: $payload['tenantName'] ?? null,
                errorMessage: $decoded['status'] === 'error' ? (string) ($decoded['message'] ?? 'AURA returned status=error.') : null,
            ),
        ];
    }

    private function result(
        bool $success,
        string $channel,
        ?int $networkLatencyMs = null,
        int $httpCode = 0,
        ?string $response = null,
        ?string $engine = null,
        ?string $model = null,
        ?int $reportedLatencyMs = null,
        ?int $tokensUsed = null,
        ?int $tokensRemaining = null,
        ?string $sessionId = null,
        ?string $tenantName = null,
        ?string $errorMessage = null,
    ): array {
        return [
            'success'           => $success,
            'httpCode'          => $httpCode,
            'channelUsed'       => $channel,
            'networkLatencyMs'  => $networkLatencyMs,
            'reportedLatencyMs' => $reportedLatencyMs,
            'response'          => $response,
            'engine'            => $engine,
            'model'             => $model,
            'tokensUsed'        => $tokensUsed,
            'tokensRemaining'   => $tokensRemaining,
            'sessionId'         => $sessionId,
            'tenantName'        => $tenantName,
            'errorMessage'      => $errorMessage,
        ];
    }
}
```

---

## 6. Notas de Gobernanza

- Este documento vive en `knowledge/Santuario_Genesis/modulos/` (o el equivalente `modulos/` del proyecto que lo siembra) como **molde agnóstico** — ningún dato real de un cliente o tenant específico debe incorporarse aquí (Mandato de Sincronización Génesis, Ley de Fricción Cero, igual que en `MOD_OPERADOR_COGNITIVO_OMNICANAL.md`).
- Este molde cubre exclusivamente el **canal de transporte M2M** (autenticación por llave estática, fallback LAN/WAN, mapeo de contrato). No define personalidad de agente, prompts de sistema, ni lógica de negocio — eso vive del lado del motor central AURA o, si el proyecto ya usa el patrón HMAC de `MOD_OPERADOR_COGNITIVO_OMNICANAL.md`, en su respectivo `.md` de conocimiento.
- Un proyecto no debe tener dos caminos de despacho activos simultáneamente sirviendo el mismo tráfico de producción (ver 1.0) — decidir cuál es el camino canónico es una decisión del Arquitecto, no una elección por defecto de la IA que siembra este molde.
- Cualquier hallazgo de una validación en vivo (Fase 4) que revele un problema de configuración (ej. llave rechazada, tenant no coincide) se documenta en el Codex del proyecto concreto, nunca en este documento agnóstico.
- (2026-07-31, v1.1) Añadidas la sección 2.1 (desambiguación de puerto multi-instancia), 3.2.1 (matriz de equivalencia de cabeceras `X-AURA-KEY`/`Authorization: Bearer`) y 4.1 (harness cURL de diagnóstico en 2 clics). Cualquier nombre comercial que un proyecto concreto le dé a una instancia satélite específica (ej. qué puerto usa, para qué línea de negocio) vive únicamente en el Codex de ese proyecto — nunca en este molde, igual que rige para "PG-AI Pink Glove AI" en `MOD_OPERADOR_COGNITIVO_OMNICANAL.md`.
- (2026-07-31, v1.2) Añadida la sección 2.2 (fallback por IP directa ante fallo de DNS del WAN, vía `CURLOPT_RESOLVE`) y actualizado el artefacto de referencia (5.4) con el tercer escalón. El molde base mantiene su criterio conservador de reintento (5.3) — la ampliación "reintentar ante cualquier fallo" documentada en un proyecto concreto sigue sin propagarse aquí.
- (2026-07-31, v1.3) Confirmado en validación real contra un servidor AURA en vivo: el payload de éxito puede venir envuelto bajo `"data"` en lugar de plano en la raíz (ver nota de compatibilidad en 3.3). El artefacto de referencia (5.4) ahora normaliza ambas formas. Este hallazgo se originó al conectar un proyecto concreto a la instancia Satélite (`:8090`) — el detalle de qué proyecto, qué puerto y qué llave se usó vive en su Codex, no aquí.
- (2026-07-31, v1.3 — Aprovisionamiento Fricción Cero) Añadida la sección 4.1.1 (procedimiento de verificación en 4 niveles: cURL crudo, ejecutor PHP CLI, endpoint HTTP de diagnóstico, logs de servidor). Formaliza como checklist reutilizable la secuencia de validación que ya exigía la regla de avance de la sección 4 — un proyecto concreto que ejecutó y documentó estos 4 niveles en su Codex fue el origen de este formalismo, pero el procedimiento en sí es agnóstico desde su primera versión aquí.
- (2026-08-03, v1.4 — Protocolo de Contexto Persistente M2M) Añadida la sección 1.4 (las 3 reglas de oro: AURA como agente autónomo, onboarding de contexto una sola vez, payload de chat ultra-liviano), la sección 3.4 (contrato provisional de onboarding — incluye un hallazgo de validación real: un servidor AURA concreto rechazó este contrato con `400`, confirmando que aún no hay un endpoint de onboarding oficial publicado) y la sección 5.5 (`syncTenantContext()` en el artefacto de referencia). Motivado por un hallazgo real de un proyecto concreto: reenviar el System Prompt completo (~10 KB) en cada mensaje de chat provocaba `502` intermitentes del lado de AURA — el detalle de qué proyecto, qué tamaño de prompt y qué latencias se midieron vive en su Codex, no aquí. **Nota de honestidad para el próximo proyecto que siembre este molde:** la migración al payload liviano no eliminó por sí sola la inestabilidad observada en el servidor AURA de referencia — un mensaje corto sin System Prompt adjunto obtuvo tanto una respuesta exitosa en ~5s como un `502` en ~30s en pruebas consecutivas. La causa raíz parece ser latencia/capacidad de inferencia inconsistente del lado de AURA, no exclusivamente el tamaño del payload — este molde documenta el protocolo correcto (reducir payload, mover el contexto a onboarding), pero no garantiza por sí solo la estabilidad del servidor central.
