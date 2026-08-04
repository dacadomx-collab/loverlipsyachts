---
modulo: MOD_OPERADOR_COGNITIVO_OMNICANAL
nombre: Operador Cognitivo Omnicanal (AI Runtime Operator)
tipo: Blueprint de Construcción — Checklist Táctico
alcance: Genérico / Agnóstico de Stack Cliente (WordPress, HTML puro, React, o cualquier lenguaje)
nucleo_inferencia: PHP 8.x / MariaDB (servidor DCD LABS)
clasificacion: Molde Reutilizable — Santuario_Genesis
version: 1.3
fecha: 2026-08-04
autoridad: Arquitecto (DCD LABS)
tono: Calma Ejecutiva (Executive Calm)
---

# MOD_OPERADOR_COGNITIVO_OMNICANAL

> Este documento es la fuente de verdad para construir, desde cero, el módulo de Operador Cognitivo Omnicanal en cualquier proyecto clonado del Santuario_Genesis. No es un chatbot: es un **AI Runtime Operator** — un motor de inferencia centralizado que atiende múltiples canales de entrada (WhatsApp, Telegram, Widget Web) bajo un único contrato de datos, sin exponer nunca la lógica de negocio, las llaves de API ni los prompts del sistema al servidor del cliente.

---

## 1. Filosofía y Arquitectura

### 1.1 Principio rector: Fricción Cero Operativa

El aislamiento de la Propiedad Intelectual (IP) es la premisa de diseño, no un añadido posterior. El conocimiento transaccional (archivos `.md`) puede vivir físicamente en el servidor del cliente, pero la inferencia, las llaves maestras y los prompts del sistema residen exclusivamente en el motor central (DCD LABS). El cliente nunca se conecta directamente a un proveedor de LLM: se conecta únicamente a un Gateway propio, autenticado criptográficamente.

### 1.2 Matriz de Aislamiento (Proxy / Puente)

| Componente | Ubicación Física | Responsabilidad |
|---|---|---|
| Frontend Widget / Webhook de Redes | Servidor del Cliente / Meta / Telegram | Captura la intención del usuario, muestra los mensajes. Interfaz agnóstica de stack. |
| Base de Conocimiento (`.md`) | Servidor del Cliente | Contiene la verdad del negocio del cliente. Consumida solo por el puente local. |
| Puente Proxy Local (PHP, ligero) | Servidor del Cliente | Inyecta la firma HMAC, lee el `.md` local con caché de memoria, dispara la llamada saliente hacia el Gateway central. |
| Motor Central de Inferencia | Servidor Linux (núcleo) | Valida la firma HMAC, aplica RAG sobre el `.md`, inyecta la personalidad del proyecto, despacha al LLM y retorna JSON. |

El puente del cliente **no razona**: solo empaqueta, firma y reenvía. Toda decisión cognitiva ocurre del otro lado del túnel.

### 1.3 Contrato JSON Unificado — Omnichannel Canonical Message Contract (OCMC)

Independientemente del canal de origen, todo evento entrante debe normalizarse a esta forma antes de tocar el motor de inferencia:

```json
{
  "ocmc_version": "1.0",
  "channel": "whatsapp | telegram | web_widget",
  "channel_message_id": "id_original_del_proveedor",
  "tenant_slug": "slug_del_proyecto_cliente",
  "contact": {
    "external_id": "identificador_en_el_canal_origen",
    "display_name": "nombre_visible_o_null"
  },
  "session_id": "uuid_de_sesion_omnicanal",
  "message": {
    "type": "text | image | audio | document | interactive",
    "text": "contenido_normalizado_o_null",
    "media_url": "url_o_null"
  },
  "received_at": "ISO8601_UTC",
  "raw_payload_ref": "referencia_opcional_al_payload_crudo_para_auditoria"
}
```

El normalizador de cada canal (WhatsApp Cloud API, Telegram Bot API, Widget Web) es responsable de producir este contrato exacto. El motor central de inferencia nunca conoce el formato nativo de cada proveedor — solo consume OCMC.

### 1.4 Arquitectura de la Fuente de Verdad (Ground Truth de Contexto)

El motor de inferencia nunca "sabe" nada por sí mismo — todo lo que puede afirmar como hecho de negocio viene de una composición determinista de tres capas, ensambladas antes de cada despacho (o registradas una sola vez del lado del motor central si el proyecto adoptó el Protocolo de Contexto Persistente M2M — ver `MOD_CONEXION_SATELLITE_AURA_M2M.md` sección 1.4):

| Capa | Contenido | Mutabilidad |
|---|---|---|
| **A. Prompt Maestro** | Persona/tono del anfitrión virtual, cerrojos comerciales (ej. "no cotizar sin datos de lead"), contrato de marcadores máquina-legibles que el post-procesador resuelve. Un solo archivo `.md` por proyecto — nunca fragmentado en múltiples prompts que puedan divergir. | Editado por un humano autorizado; cambia con poca frecuencia. |
| **B. Catálogo Estructurado (Base de Datos)** | Inventario/catálogo real del negocio (productos, unidades, tarifas) — vive en una tabla relacional, **no** hardcodeado en el prompt maestro. Se inyecta en el prompt en tiempo de ensamblaje vía un marcador `{{NOMBRE_TABLA_PLACEHOLDER}}` que el puente/motor sustituye por una tabla markdown generada dinámicamente desde la fila `verified` de esa tabla. | Editable por el propietario del negocio desde su panel de control (ver Fase 8) — cambia con frecuencia, sin requerir tocar el prompt maestro ni desplegar código. |
| **C. Reglas de Negocio y FAQs** | Políticas operativas (reserva, clima, cancelación), rutas/experiencias/servicios verificados que el motor puede describir libremente sin necesitar los 4 datos de un lead. Vive dentro del mismo Prompt Maestro (capa A) como sección propia, no en un archivo separado — evita que dos documentos de "verdad" puedan contradecirse. | Mismo ciclo de edición que la capa A. |

**Regla no negociable (Mandamiento 4 — Anti-Alucinación, agnóstico):** ninguna fila de la Capa B sin marcar como verificada por un humano puede llegar al motor de inferencia como hecho citable — el ensamblador debe filtrar por el estado de verificación antes de construir el marcador `{{...}}`, nunca confiar en que el motor "sabrá" ignorar datos no confirmados. Un catálogo vacío o sin filas verificadas se resuelve a una frase honesta ("catálogo temporalmente no disponible"), nunca a una tabla vacía ni a un dato inventado.

### 1.5 Fallback Cognitivo Multi-Proveedor (Opcional — Fase 2 del roadmap)

Un proyecto puede, opcionalmente, configurar un proveedor de LLM externo genérico (ej. OpenAI/Claude/equivalente) como última instancia de respaldo, **solo** si el canal directo hacia el motor central (Fase 0-1 de este documento, o el satélite M2M de `MOD_CONEXION_SATELLITE_AURA_M2M.md`) falla por completo. Este fallback:

- Se marca explícitamente como **"Fase 2 / Pausada"** hasta que el canal directo esté probado y estable en producción — priorizar la ruta principal es una decisión de gobernanza, no una limitación técnica.
- Nunca comparte el mecanismo de contexto persistente del canal principal (si el proyecto lo adoptó — sección 1.4 de `MOD_CONEXION_SATELLITE_AURA_M2M.md`); al ser una ruta de último recurso, de baja frecuencia de uso, el costo de reenviar el contexto completo (Capas A+B+C de la sección 1.4 de este documento) en cada llamada es aceptable.
- Requiere su propia llave de API, gestionada con el mismo estándar de aislamiento que cualquier credencial de proveedor (nunca expuesta al navegador, nunca en logs, editable solo desde el panel de Super Admin — ver Fase 8).
- No se considera "validado" ni se documenta como ruta de producción confiable hasta haberse probado con una llave real contra el proveedor real — una implementación sin esa prueba en vivo se marca explícitamente como "implementado, pendiente de validación" en el Codex del proyecto concreto, nunca como cerrado.

---

## 2. Checklist de Ejecución — Fases Operativas

> Regla de avance: ninguna fase se marca cerrada sin verificación funcional. No saltar fases (Mandamiento de Ejecución Determinística).

### Fase 0 — Aprovisionamiento Fricción Cero (Onboarding en 3 Clics)

> Esta fase precede a la Fase 1: es el proceso interno de AXON/ACADEP para generar y entregar el artefacto que el cliente instalará. Ocurre enteramente en el Dashboard TDTM — el cliente no ve, ni necesita entender, nada de lo descrito aquí.

**Nota de gobernanza sobre el `shared_secret` generado:** el secreto descrito en esta fase es un **token de túnel escopado por tenant** (identifica y autentica a *ese* cliente frente al Gateway), no una credencial maestra. Las llaves reales de los proveedores de LLM (OpenAI/Claude/etc.) nunca salen del motor central y jamás forman parte de este artefacto — ver punto 0.4. Esta distinción es lo que permite que el artefacto se empaquete con su secreto de túnel embebido sin violar la Bóveda de Secretos (Mandamiento 12), que rige sobre credenciales maestras y llaves de API de terceros.

- [ ] **0.1 Generación de Credenciales por Tenant**
  - [ ] Al registrar un nuevo proyecto/cliente en el Dashboard TDTM, generar automáticamente un `X-Tenant-Id` (slug único, legible, derivado del nombre del proyecto + sufijo aleatorio para evitar colisiones).
  - [ ] Generar en el mismo acto un `shared_secret` aleatorio criptográficamente seguro (mínimo 256 bits de entropía), asociado 1:1 al `X-Tenant-Id`.
  - [ ] Persistir el `shared_secret` cifrado (AES-256-GCM, vía el mecanismo de bóveda del motor central) en la tabla de tenants — nunca en texto plano, ni siquiera en la base de datos interna.
  - [ ] Marcar el registro del tenant como `provisioning_status = pending` hasta que el artefacto sea generado y confirmado como desplegado.

- [ ] **0.2 Empaquetado Automático (El Instalador)**
  - [ ] El Dashboard TDTM debe exponer una función de "Compilar Túnel de Conexión" que tome como entrada el `X-Tenant-Id` y su `shared_secret` recién generados.
  - [ ] Compilar dos variantes de artefacto de entrega, según el nivel de acceso del cliente al hosting:
    - [ ] **Artefacto A — `proxy_bridge.php` comprimido en ZIP:** copia de la clase `ProxyBridge` (Fase 1) con el `X-Tenant-Id` y el `shared_secret` inyectados en su bloque de configuración local en el momento de la compilación — no en el repositorio fuente del molde, que permanece genérico. Se entrega listo para subir vía FTP/SFTP a la raíz del hosting del cliente.
    - [ ] **Artefacto B — snippet `<script>` autoejecutable:** para clientes sin acceso a backend PHP propio (landing estática, WordPress sin plugin custom), un tag `<script>` que apunta al Widget Web servido desde infraestructura de AXON, con el `X-Tenant-Id` embebido como atributo de datos (`data-tenant-id`) — el `shared_secret` en este caso **nunca** viaja al navegador; la autenticación del túnel ocurre servidor a servidor entre la infraestructura del widget alojado y el Gateway central.
  - [ ] Cada artefacto compilado queda registrado con un hash de verificación y una fecha de expiración de instalación (ventana razonable, ej. 72h) — si no se confirma el despliegue en ese plazo, el `shared_secret` se rota automáticamente antes de reintentar.

- [ ] **0.3 Despliegue Rápido — Los 3 Clics**
  - [ ] **Clic 1 — Registrar:** el operador de AXON da de alta al prospecto/cliente en el Dashboard TDTM (nombre, dominio, canal(es) deseados). Esto dispara 0.1 y habilita el espacio del tenant.
  - [ ] **Clic 2 — Generar y Compilar:** el operador confirma los canales activos (WhatsApp/Telegram/Widget Web) y el Dashboard ejecuta 0.2, produciendo el artefacto correspondiente listo para descarga.
  - [ ] **Clic 3 — Inyectar:** según el caso:
    - [ ] Si AXON gestiona el hosting o tiene credenciales FTP autorizadas del cliente, el propio Dashboard sube el Artefacto A vía FTP automatizado a la ruta acordada.
    - [ ] Si no, se entrega al cliente únicamente el tag `<script>` (Artefacto B) para que lo pegue en su HTML o en el editor de su WordPress — sin exponerlo a ningún archivo de configuración, credencial, ni paso técnico adicional.
  - [ ] Al completarse el paso 3, el Dashboard marca `provisioning_status = active` tras recibir el primer *heartbeat* válido del túnel (ver 0.4).

- [ ] **0.4 Consumo de Tokens AURA (Motor Central Linux)**
  - [ ] Desde el primer mensaje procesado, todo el consumo de tokens de inferencia se registra y factura contra el `X-Tenant-Id`, nunca contra una llave visible al cliente.
  - [ ] Las llaves reales de los proveedores de LLM viven exclusivamente en el motor central (Linux, DCD LABS) y son compartidas entre todos los tenants a nivel de infraestructura — el cliente jamás recibe, ve, ni puede extraer una llave de OpenAI/Claude/equivalente, ni siquiera inspeccionando el artefacto instalado en su servidor.
  - [ ] El artefacto instalado en el cliente solo posee su `shared_secret` de túnel — revocable y rotable de forma independiente, sin impacto en el resto de tenants ni en las llaves maestras.
  - [ ] Registrar el consumo de tokens por tenant en la telemetría central para fines de facturación y de la Matriz Financiera del proyecto (ver `05_MATRIZ_FINANCIERA_Y_VENTAS.md`), sin exponer ese detalle en ninguna respuesta JSON hacia el servidor del cliente.

### Fase 1 — Infraestructura del Cliente (El Escudo y el Proxy)

- [ ] Crear la clase `ProxyBridge` (PHP 8.x estricto, sin dependencias externas, solo cURL nativo) en el servidor del cliente.
- [ ] Implementar lectura de archivos `.md` locales con `file_get_contents()` protegida por `LOCK_EX` en escritura concurrente.
- [ ] Implementar caché en memoria volátil de dos niveles:
  - [ ] **Nivel APCu** para el contenido parseado del `.md` (clave por hash de ruta + mtime, invalidación automática si el archivo cambia).
  - [ ] **Nivel OPcache** (opcional, alto rendimiento): transformar el `.md` en un array PHP precompilado (`knowledge_base_arrays/*.php`) y precalentarlo vía `opcache_compile_file()` en un script `preload.php`.
- [ ] Configurar `php.ini` de producción del cliente (si el hosting lo permite) con `opcache.validate_timestamps=0` únicamente si existe un proceso de invalidación manual post-deploy; de lo contrario, mantener validación de timestamps activa para evitar servir conocimiento obsoleto.
- [ ] Implementar generación de firma `HMAC-SHA256` sobre el payload saliente usando un `shared_secret` almacenado fuera del árbol web (nunca hardcodeado, nunca en `.env` accesible vía HTTP).
- [ ] Empaquetar en el payload saliente: mensaje del usuario (ya normalizado a OCMC), contenido relevante del `.md`, y metadatos de sesión.
- [ ] Disparar la petición saliente vía cURL nativo hacia el Gateway central, con:
  - [ ] Timeout de conexión estricto (≤ 3s).
  - [ ] Timeout de lectura estricto (≤ 8s, por debajo de la ventana de tolerancia de reintentos de canales como WhatsApp).
  - [ ] Manejo de excepciones try/catch completo — ningún fallo de red debe producir un error 500 visible al usuario final; degradar a un mensaje de espera controlado.
- [ ] Verificar que el puente no contiene lógica de negocio, prompts, ni credenciales de LLM — su única responsabilidad es leer, firmar y reenviar.

### Fase 2 — Base de Datos Omnicanal (Core Central)

- [ ] Diseñar y desplegar las tablas relacionales en `snake_case`, validadas contra el Codex del proyecto antes de ejecutar cualquier `CREATE TABLE`:
  - [ ] `omnichannel_channels` — catálogo de canales activos por tenant (`whatsapp`, `telegram`, `web_widget`), credenciales cifradas de cada integración, estado (`active`/`inactive`).
  - [ ] `omnichannel_sessions` — hilo de conversación por contacto/canal, con `tenant_id`, `channel_id`, `contact_id`, timestamps de inicio/última actividad, estado (`open`/`closed`).
  - [ ] `omnichannel_contacts` — identidad del usuario final por canal, con `external_id` (id nativo del canal), `display_name`, `tenant_id`.
  - [ ] `omnichannel_messages` — historial de mensajes, con `session_id`, `direction` (`inbound`/`outbound`), `content`, `channel_message_id` (para idempotencia), `created_at`.
- [ ] Confirmar con el Arquitecto si estas tablas son nuevas o si existe un mapeo a estructuras ya existentes en el Codex del proyecto (Mandamiento de Inmutabilidad del Sistema — no crear tablas sin autorización explícita).
- [ ] Añadir índices de idempotencia sobre `channel_message_id` para prevenir procesamiento duplicado de eventos reenviados por el proveedor.
- [ ] Documentar el schema resultante en el pilar de Codex correspondiente del proyecto clonado.

### Fase 3 — Gateway y Contrato OCMC

- [ ] Crear el endpoint público único de entrada (Gateway) que resuelve el tenant vía `slug` recibido en la cabecera de autenticación.
- [ ] Implementar un normalizador por canal, cada uno responsable de transformar el payload nativo del proveedor al contrato OCMC (sección 1.3):
  - [ ] Normalizador WhatsApp Cloud API.
  - [ ] Normalizador Telegram Bot API.
  - [ ] Normalizador Widget Web (JSON directo, ya cercano a OCMC por diseño).
- [ ] Validar que ningún normalizador altere las propiedades del contrato OCMC una vez definido (Contrato de API Estricto) — cualquier campo nuevo requiere versión incremental (`ocmc_version`).
- [ ] Persistir el evento normalizado en `omnichannel_messages` antes de despachar al motor de inferencia, garantizando trazabilidad incluso si la inferencia falla.
- [ ] Retornar al canal de origen únicamente lo que ese canal exige como acuse de recibo (ver Fase 5, handshake y ventanas de timeout).

### Fase 4 — Motor de Encolamiento Asíncrono

- [ ] Adoptar el patrón **Queue-First**: el Gateway responde en milisegundos (`HTTP 200 OK`, ausente de procesamiento pesado) y delega la inferencia a un worker en segundo plano.
- [ ] Elegir mecanismo de cola según la infraestructura disponible del cliente/hosting:
  - [ ] Redis (preferido si hay acceso a un proceso persistente o VPS).
  - [ ] Tabla de persistencia transaccional + Worker CLI (fallback si el hosting es compartido y no permite procesos daemon — patrón ya usado en `knowledge_candidates`/`extraer_aprendizaje.php` de este mismo ecosistema).
- [ ] Implementar verificación de idempotencia antes de encolar: comprobar `channel_message_id` contra el TTL de la cola o contra `omnichannel_messages` para descartar reenvíos duplicados (entrega "al menos una vez" es el comportamiento estándar de estos proveedores).
- [ ] Implementar el Worker CLI que:
  - [ ] Consume el mensaje encolado.
  - [ ] Ejecuta la inferencia cognitiva (RAG sobre el `.md`, despacho al LLM).
  - [ ] Realiza la llamada saliente de respuesta hacia el canal original (API de envío de WhatsApp/Telegram, o push al Widget Web vía polling/SSE).
  - [ ] Registra telemetría de latencia y estado en el log de actividad del sistema.
- [ ] Verificar que el Worker corre exclusivamente vía CLI (`php_sapi_name() !== 'cli'` → rechazo), nunca accesible vía HTTP directo.

### Fase 5 — Blindaje Perimetral y Seguridad

- [ ] Implementar validación de firma criptográfica en cada evento entrante:
  - [ ] WhatsApp: `X-Hub-Signature-256`, calculada con `hash_hmac('sha256', $rawBody, $appSecret)` sobre el cuerpo crudo (`php://input`), comparada con `hash_equals()`.
  - [ ] Túnel interno Cliente → Gateway: firma HMAC-SHA256 de tres factores (`X-Tenant-Id`, `X-Signature`, `X-Timestamp` con ventana de tolerancia ±300s) + anti-replay (nonce con TTL corto en APCu o equivalente).
- [ ] Implementar el handshake de verificación de webhook (`GET` con `hub.mode`, `hub.verify_token`, `hub.challenge`) respondiendo en texto plano y comparando el token con `hash_equals()` (protección contra ataques de temporización).
- [ ] Evaluar si el hosting del cliente requiere mTLS saliente hacia Meta; si aplica, actualizar el almacén de confianza con la Autoridad de Certificación vigente del proveedor (verificar fecha de vigencia antes de asumir el certificado histórico).
- [ ] Implementar validación opcional de rango de IP de origen mediante coincidencia CIDR (`ip2long()` + máscara de bits) contra la lista oficial de rangos publicada por el proveedor del canal, refrescada periódicamente — nunca hardcodeada de forma permanente.
- [ ] Si el hosting del cliente presenta estrangulamiento de red conocido hacia el proveedor del canal (latencias o bloqueos intermitentes en el handshake), documentar la opción de un Servidor Puente (proxy inverso en un proveedor de infraestructura alterno) como mitigación, sin asumirlo como requisito por defecto — es una decisión de infraestructura caso por caso, no un paso obligatorio de este blueprint.
- [ ] Confirmar que ninguna credencial (App Secret, `shared_secret`, tokens de canal) queda hardcodeada en código — todo vía variable de entorno inyectada en servidor.

### Fase 6 — Integración al Dashboard TDTM

- [ ] Exponer en el panel de administración del ecosistema una vista de canales activos por tenant, consumiendo `omnichannel_channels`.
- [ ] Mostrar métricas de volumen y estado por canal (mensajes entrantes/salientes, sesiones abiertas, tiempo de respuesta) sin exponer en el JSON de respuesta ningún campo de telemetría interna reservado (latencias de red, estado de PDO, tokens en vuelo) salvo que la ley de observabilidad vigente del ecosistema lo autorice explícitamente en entornos no productivos.
- [ ] Conectar el estado de conexión de cada canal (`active`/`inactive`, último heartbeat) a un indicador visual en el dashboard.
- [ ] Verificar que todo `fetch()` del dashboard hacia los endpoints de este módulo maneja `401` redirigiendo a login — CORS nunca sustituye autenticación real.
- [ ] Validar, antes de cerrar el hito, que las latencias de encolamiento y despacho del módulo se observan saludables desde el panel de observabilidad del ecosistema (si el proyecto clonado cuenta con uno).

### Fase 7 — Módulo de Enlaces Efímeros y Protección de Información Exclusiva (Self-Destruct Link Architecture)

> Ver sección 6 para el diseño conceptual agnóstico y sección 3.5 para el artefacto de código de referencia. Este módulo es independiente del Operador Cognitivo Omnicanal en sí — no requiere canales de mensajería activos — pero comparte su misma premisa de aislamiento de IP: información privada de un cliente (cotización, itinerario, propuesta) nunca debe quedar accesible de forma indefinida solo por conocer una URL.

- [ ] Diseñar y validar contra el Codex del proyecto la tabla de tokens efímeros (ver sección 3.1 del checklist de esta fase) antes de cualquier `CREATE TABLE` — mismo Mandamiento de Inmutabilidad del Sistema que rige la Fase 2.
- [ ] Implementar la generación de token con entropía criptográfica suficiente (mínimo 256 bits), codificado en un alfabeto seguro para URL.
- [ ] Implementar el conteo atómico de accesos como una única sentencia `UPDATE` condicionada (`WHERE ... AND vistas_actuales < vistas_maximas`), nunca como un `SELECT` seguido de un `UPDATE` separado — esa secuencia permite que dos lecturas simultáneas consuman la "última vista" al mismo tiempo.
- [ ] Definir el valor por defecto de vistas antes de autodestrucción (recomendado: 3) como un ajuste global editable desde el panel de administración, no hardcodeado.
- [ ] Permitir además un límite de vistas específico por enlace, editable mientras el enlace siga activo, sin permitir reducirlo por debajo de las vistas ya consumidas.
- [ ] Implementar un estado explícito de revocación manual, independiente del agotamiento natural del contador.
- [ ] Implementar el endpoint público de redención: entrega el contenido (o redirige a él) solo si el enlace sigue activo y tiene vistas disponibles; en cualquier otro caso, responde con una página de cortesía (nunca un error técnico crudo) indicando que el enlace expiró.
- [ ] Integrar al panel de administración: creación de enlaces, listado de enlaces activos con su conteo de vistas, edición del límite por enlace, ajuste del valor global por defecto, y revocación manual.
- [ ] Verificar que ningún dato del contenido protegido (cotización, itinerario) queda expuesto en logs de acceso ni en el cuerpo de un error 500.

**Criterios de aceptación:**
- [ ] Dos solicitudes simultáneas contra un enlace con una sola vista restante nunca resultan en ambas obteniendo el contenido — una gana, la otra recibe la página de expiración.
- [ ] El propietario puede cambiar el límite global de vistas para enlaces futuros sin necesidad de una migración de base de datos ni de tocar código.
- [ ] Un enlace revocado manualmente deja de funcionar de inmediato, incluso si aún le quedaban vistas disponibles.

### Fase 8 — Panel de Administración Super Admin (Cockpit)

> Complementa la Fase 6 (Integración al Dashboard TDTM): mientras la Fase 6 cubre la vista operativa general (canales activos, métricas), esta fase define el **cockpit técnico** — la superficie de configuración y contenido que un operador con más contexto técnico necesita, separada de la vista que el propietario del negocio usa día a día.

- [ ] **8.1 Separación de roles — vista de propietario vs. vista de operador técnico**
  - [ ] Definir al menos dos roles: uno business-facing (propietario del negocio — ve leads, catálogo, plantillas) y uno técnico (super admin — ve además credenciales de canal y configuración de motor de inferencia).
  - [ ] La sección de configuración de credenciales/motor de inferencia nunca se renderiza para el rol business-facing, ni siquiera oculta vía CSS — debe estar ausente del HTML servido, para que no exista superficie que un usuario sin ese rol pueda inspeccionar.
  - [ ] La promoción de una cuenta al rol técnico es siempre una acción manual explícita (nunca automática ni por defecto) — un despliegue nuevo no debe escalar privilegios de ninguna cuenta existente sin intervención humana.

- [ ] **8.2 Panel de credenciales de canal de mensajería**
  - [ ] Formulario para las credenciales del canal de mensajería activo (ej. token de acceso, token de verificación de webhook, identificador de número/cuenta de envío) — cada campo tipo secreto se enmascara al mostrarse (solo los últimos caracteres visibles) y nunca se re-envía en claro hacia el navegador tras guardarse.
  - [ ] Solo una whitelist fija de claves es editable desde este panel — nunca un editor de configuración genérico de propósito abierto.
  - [ ] Guardar una credencial nueva no debe requerir despliegue de código ni reinicio del proceso del servidor web.

- [ ] **8.3 Gestor de plantillas de respuesta automática para leads**
  - [ ] Catálogo de plantillas de mensaje reutilizables (ej. confirmación de recepción, seguimiento de cotización) por canal de salida (correo electrónico, mensajería instantánea) — cada plantilla soporta el mismo patrón de bilingüismo de par de nodos que el resto del ecosistema, si el proyecto lo requiere (ver sección 5.7 para el mismo principio aplicado a enlaces efímeros).
  - [ ] Las plantillas son contenido editable por el operador — nunca hardcodeadas en el código del motor de despacho.
  - [ ] Igual que en la sección 5.7: ninguna plantilla real (copy de negocio) vive en este documento agnóstico — el contenido concreto se registra en el Codex del proyecto que lo usa.

- [ ] **8.4 Gestor visual del catálogo (CRUD)**
  - [ ] Interfaz para crear, editar y eliminar filas del catálogo estructurado (Capa B de la sección 1.4) — formulario + tabla, sin requerir acceso directo a la base de datos.
  - [ ] Toda fila nueva entra en estado "no verificado" por defecto, sin importar qué envíe el cliente — solo una edición posterior explícita de un humano la promueve a "verificada" y, por lo tanto, citable por el motor de inferencia (mismo cerrojo anti-alucinación de la sección 1.4, ahora reforzado server-side en el endpoint de escritura, no solo confiado al frontend).
  - [ ] Las acciones de escritura (crear/editar/eliminar) deben distinguirse de las de solo lectura (listar) en cualquier mecanismo de protección anti-CSRF de la sesión — ver nota de la Fase 5 sobre rotación de tokens.

- [ ] **8.5 Visor de leads/conversaciones en tiempo real**
  - [ ] Tabla de leads/conversaciones capturados, ordenada por actividad más reciente, con contacto, canal de origen, último mensaje, y una marca visual para perfiles de alto valor (ver Escalación de Guante Blanco u homólogo del proyecto concreto).
  - [ ] Esta vista es de solo lectura — cualquier acción de escritura (marcar como atendido, escalar) es una acción explícita separada, nunca implícita al simplemente listar.

**Nota de concurrencia (aplica a toda esta fase y a la Fase 6):** si el panel carga varios paneles/tablas de forma simultánea al abrir la página (leads + plantillas + catálogo + credenciales, cada uno con su propia llamada de "listar"), y el mecanismo anti-CSRF del proyecto usa un token de sesión que rota en cada petición, **las llamadas de solo lectura no deben rotar ese token** — solo las que mutan estado. Rotar en una lectura invalida el token que las demás llamadas concurrentes ya llevaban consigo, produciendo fallos de "token inválido" que parecen un bug de JavaScript pero son una condición de carrera de sesión del lado del servidor. Este hallazgo se confirmó en un proyecto concreto — el detalle de la reproducción vive en su Codex, no aquí.

---

## 3. Artefactos de Código e Infraestructura (Inyección Directa)

> Esta sección traduce el checklist conceptual de la Fase 1, 2, 4 y 5 en artefactos ejecutables listos para adaptar al `X-Tenant-Id` real del proyecto. Todo bloque aquí es **genérico por diseño**: ningún hostname, dominio, `shared_secret` ni credencial real de AXON_DCD debe sustituir los placeholders al momento de sembrar este archivo en un proyecto clonado. Un desarrollador o IA externa sin contexto previo debe poder copiar, adaptar el placeholder y desplegar en menos de una hora.

### 3.1 Bloque SQL DDL — Schema Omnicanal (Fase 2)

MariaDB/MySQL estricto, `snake_case`, con `CREATE TABLE IF NOT EXISTS` para permitir ejecución idempotente en entornos ya parcialmente aprovisionados. Incluye dos tablas adicionales respecto al checklist original (`omnichannel_message_attachments` y `omnichannel_webhooks`) requeridas para que el código de las secciones 3.2–3.4 tenga soporte real de adjuntos y de registro de eventos entrantes crudos.

```sql
-- ============================================================
-- MOD_OPERADOR_COGNITIVO_OMNICANAL — Schema Base (snake_case)
-- Ejecutar solo tras validación contra el Codex del proyecto.
-- ============================================================

CREATE TABLE IF NOT EXISTS omnichannel_channels (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           VARCHAR(64)     NOT NULL,
    channel_type        ENUM('whatsapp','telegram','web_widget') NOT NULL,
    channel_label       VARCHAR(120)    NOT NULL,
    credentials_encrypted TEXT          NOT NULL COMMENT 'Cifrado AES-256-GCM, nunca texto plano',
    status              ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tenant_channel (tenant_id, channel_type),
    INDEX idx_channel_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS omnichannel_contacts (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           VARCHAR(64)     NOT NULL,
    channel_id          BIGINT UNSIGNED NOT NULL,
    external_id         VARCHAR(190)    NOT NULL COMMENT 'ID nativo del canal de origen',
    display_name        VARCHAR(190)    NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_channel_external (channel_id, external_id),
    INDEX idx_contact_tenant (tenant_id),
    CONSTRAINT fk_contact_channel FOREIGN KEY (channel_id)
        REFERENCES omnichannel_channels(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS omnichannel_sessions (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           VARCHAR(64)     NOT NULL,
    channel_id          BIGINT UNSIGNED NOT NULL,
    contact_id          BIGINT UNSIGNED NOT NULL,
    session_uuid        CHAR(36)        NOT NULL,
    status              ENUM('open','closed') NOT NULL DEFAULT 'open',
    started_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_activity_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_session_uuid (session_uuid),
    INDEX idx_session_contact (contact_id),
    INDEX idx_session_status (status, last_activity_at),
    CONSTRAINT fk_session_channel FOREIGN KEY (channel_id)
        REFERENCES omnichannel_channels(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_session_contact FOREIGN KEY (contact_id)
        REFERENCES omnichannel_contacts(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS omnichannel_messages (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           VARCHAR(64)     NOT NULL,
    session_id          BIGINT UNSIGNED NOT NULL,
    channel_message_id  VARCHAR(190)    NOT NULL COMMENT 'ID nativo del proveedor, para idempotencia',
    direction            ENUM('inbound','outbound') NOT NULL,
    message_type        ENUM('text','image','audio','document','interactive') NOT NULL DEFAULT 'text',
    content              TEXT            NULL,
    ocmc_payload         JSON            NULL COMMENT 'Payload OCMC completo, para auditoría',
    processing_status    ENUM('queued','processing','delivered','failed') NOT NULL DEFAULT 'queued',
    created_at            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_channel_message (channel_message_id),
    INDEX idx_message_session (session_id, created_at),
    INDEX idx_message_status (processing_status),
    CONSTRAINT fk_message_session FOREIGN KEY (session_id)
        REFERENCES omnichannel_sessions(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS omnichannel_message_attachments (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id          BIGINT UNSIGNED NOT NULL,
    media_type           VARCHAR(60)     NOT NULL COMMENT 'mime type o categoría del proveedor',
    media_url             VARCHAR(500)    NULL,
    media_ref             VARCHAR(190)    NULL COMMENT 'ID de media nativo del proveedor si aplica',
    created_at             DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attachment_message (message_id),
    CONSTRAINT fk_attachment_message FOREIGN KEY (message_id)
        REFERENCES omnichannel_messages(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS omnichannel_webhooks (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           VARCHAR(64)     NOT NULL,
    channel_type        ENUM('whatsapp','telegram','web_widget') NOT NULL,
    raw_payload          JSON            NOT NULL COMMENT 'Payload crudo del proveedor, previo a normalización OCMC',
    signature_valid       TINYINT(1)      NOT NULL DEFAULT 0,
    received_at            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_webhook_tenant (tenant_id, received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.2 Clase PHP `ProxyBridge.php` (Fase 1)

PHP 8.x estricto, cero dependencias externas, cURL nativo. Lectura del `.md` con caché APCu por `mtime` y fallback directo a disco con `LOCK_EX`. Firma HMAC-SHA256 de cuatro cabeceras. Timeouts estrictos y degradación controlada.

```php
<?php
declare(strict_types=1);

/**
 * ProxyBridge — Puente Local del AI Runtime Operator.
 * Vive en el servidor del cliente. No razona, no decide: solo lee, firma y reenvía.
 * Molde genérico — sustituir los placeholders TENANT_ID / SHARED_SECRET / GATEWAY_URL
 * al momento de compilar el artefacto por tenant (ver Fase 0.2).
 */
final class ProxyBridge
{
    private const GATEWAY_URL     = 'https://[GATEWAY_HOST]/api/public/ai_runtime_gateway.php';
    private const TENANT_ID       = '[X_TENANT_ID]';
    private const SHARED_SECRET   = '[SHARED_SECRET]'; // Token de túnel escopado por tenant — no una llave maestra.
    private const CONNECT_TIMEOUT = 3;
    private const READ_TIMEOUT    = 8;
    private const APCU_TTL        = 300;

    public function __construct(private readonly string $knowledgeMdPath)
    {
    }

    /**
     * Punto de entrada: recibe el mensaje de usuario ya en formato OCMC parcial
     * (channel, contact, session_id, message) y retorna la respuesta del motor central.
     */
    public function forward(array $ocmcMessage): array
    {
        try {
            $knowledge = $this->readKnowledgeBase();
            $body = json_encode([
                'ocmc_version' => '1.0',
                'tenant_slug'  => self::TENANT_ID,
                'knowledge'    => $knowledge,
            ] + $ocmcMessage, JSON_THROW_ON_ERROR);

            return $this->dispatch($body);
        } catch (\Throwable $e) {
            error_log('[ProxyBridge] Degradación controlada: ' . $e->getMessage());
            return [
                'status'      => 'degraded',
                'message_key' => 'degraded_fallback', // Resuelto a copy localizada por tenant/idioma en la capa de presentación — nunca un string fijo aquí.
            ];
        }
    }

    /**
     * Lectura del .md con caché APCu por mtime. Fallback a disco con LOCK_EX
     * si APCu no está disponible o hay cache miss.
     */
    private function readKnowledgeBase(): string
    {
        if (!is_readable($this->knowledgeMdPath)) {
            throw new \RuntimeException('Archivo de conocimiento no accesible: ' . $this->knowledgeMdPath);
        }

        $mtime   = (string) filemtime($this->knowledgeMdPath);
        $cacheKey = 'proxy_bridge_md_' . self::TENANT_ID . '_' . md5($this->knowledgeMdPath) . '_' . $mtime;

        if (function_exists('apcu_fetch')) {
            $hit = apcu_fetch($cacheKey, $success);
            if ($success) {
                return $hit;
            }
        }

        $fh = fopen($this->knowledgeMdPath, 'rb');
        if ($fh === false) {
            throw new \RuntimeException('No fue posible abrir el archivo de conocimiento.');
        }

        try {
            if (!flock($fh, LOCK_EX)) {
                throw new \RuntimeException('No fue posible bloquear el archivo de conocimiento.');
            }
            $content = stream_get_contents($fh);
            flock($fh, LOCK_UN);
        } finally {
            fclose($fh);
        }

        if ($content === false) {
            throw new \RuntimeException('Lectura fallida del archivo de conocimiento.');
        }

        if (function_exists('apcu_store')) {
            apcu_store($cacheKey, $content, self::APCU_TTL);
        }

        return $content;
    }

    /**
     * Firma HMAC-SHA256 de tres factores + nonce anti-replay, y despacho cURL nativo.
     */
    private function dispatch(string $body): array
    {
        $timestamp = (string) time();
        $nonce     = bin2hex(random_bytes(16));
        $signature = hash_hmac('sha256', $timestamp . $nonce . $body, self::SHARED_SECRET);

        $ch = curl_init(self::GATEWAY_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => self::READ_TIMEOUT,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-Tenant-Id: ' . self::TENANT_ID,
                'X-Signature: ' . $signature,
                'X-Timestamp: ' . $timestamp,
                'X-Nonce: ' . $nonce,
            ],
        ]);

        $response  = curl_exec($ch);
        $errno     = curl_errno($ch);
        $error     = curl_error($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            throw new \RuntimeException('Fallo de red hacia el Gateway central: ' . $error);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException('Gateway central respondió con estado ' . $httpCode);
        }

        $decoded = json_decode((string) $response, true, 512, JSON_THROW_ON_ERROR);
        return is_array($decoded) ? $decoded : [];
    }
}
```

### 3.3 Handshake, Firma, y Round-Trip Completo — Meta WhatsApp Cloud API (Fases 3 + 5)

Flujo de referencia de punta a punta para el canal WhatsApp, uniendo las Fases 3 (Gateway/OCMC) y 5 (Blindaje) en una sola narrativa: **(1)** Meta llama `GET` al endpoint del webhook una sola vez para confirmar la suscripción (`handleMetaWebhookHandshake`); **(2)** cada evento entrante real llega por `POST`, se valida su firma contra el cuerpo crudo (`validateMetaSignature`) antes de tocar cualquier lógica de negocio; **(3)** el payload nativo de Meta se normaliza al contrato OCMC (sección 1.3); **(4)** se despacha al motor de inferencia (vía el puente de la sección 3.2, o síncronamente desde el worker de la sección 3.4 si el proyecto adoptó el patrón Queue-First); **(5)** la respuesta se reenvía al mismo hilo de WhatsApp mediante una llamada saliente a la Graph API. Las cuatro funciones siguientes son el artefacto de referencia de ese flujo — el endpoint concreto del proyecto (`api/public/[nombre]_webhook.php` o equivalente) las orquesta en ese orden.

```php
<?php
declare(strict_types=1);

/**
 * Endpoint público de verificación de webhook (GET) — api/public/whatsapp_webhook.php
 * Meta convierte automáticamente los puntos de sus parámetros a guiones bajos
 * en $_GET (hub.mode → hub_mode) al poblar la superglobal en PHP.
 */
function handleMetaWebhookHandshake(string $expectedVerifyToken): void
{
    $mode      = $_GET['hub_mode'] ?? '';
    $token     = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if ($mode === 'subscribe' && hash_equals($expectedVerifyToken, (string) $token)) {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(200);
        echo $challenge;
        exit;
    }

    http_response_code(403);
    exit;
}

/**
 * Validación de firma criptográfica de eventos entrantes (POST).
 * Debe evaluarse SIEMPRE contra el cuerpo crudo (php://input), nunca contra
 * una re-serialización del payload — cualquier reordenamiento de propiedades
 * o escape distinto corrompe la coincidencia de bytes de la firma de Meta.
 */
function validateMetaSignature(string $appSecret): bool
{
    $signatureHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    if ($signatureHeader === '') {
        return false;
    }

    $parts = explode('=', $signatureHeader, 2);
    if (count($parts) !== 2 || strtolower($parts[0]) !== 'sha256') {
        return false;
    }

    $receivedHash   = $parts[1];
    $rawBody        = file_get_contents('php://input');
    $calculatedHash = hash_hmac('sha256', (string) $rawBody, $appSecret);

    return hash_equals($calculatedHash, $receivedHash);
}

/**
 * Normaliza un evento entrante nativo de WhatsApp Cloud API al contrato
 * OCMC (sección 1.3). Meta anida el mensaje real varios niveles dentro
 * de `entry[0].changes[0].value.messages[0]` — un payload sin mensajes
 * (ej. un recibo de estado "delivered"/"read") es válido y debe
 * descartarse sin error, no tratarse como un fallo de normalización.
 */
function normalizeWhatsAppToOcmc(array $rawPayload, string $tenantSlug): ?array
{
    $value = $rawPayload['entry'][0]['changes'][0]['value'] ?? null;
    $msg   = $value['messages'][0] ?? null;
    if ($msg === null) {
        return null; // Recibo de estado u otro evento sin contenido de mensaje — no es un error.
    }

    $contactName = $value['contacts'][0]['profile']['name'] ?? null;

    return [
        'ocmc_version'       => '1.0',
        'channel'            => 'whatsapp',
        'channel_message_id' => $msg['id'],
        'tenant_slug'        => $tenantSlug,
        'contact'            => [
            'external_id'  => $msg['from'],
            'display_name' => $contactName,
        ],
        'session_id' => $msg['from'], // WhatsApp no tiene un concepto de sesión propio — el número del remitente es el identificador de hilo estable.
        'message'    => [
            'type'      => $msg['type'] ?? 'text',
            'text'      => $msg['text']['body'] ?? null,
            'media_url' => null, // Resolución de media_id → URL descargable es responsabilidad de un paso posterior, no de la normalización.
        ],
        'received_at'     => gmdate('Y-m-d\TH:i:s\Z'),
        'raw_payload_ref' => null, // El llamador decide si persiste el crudo (ver omnichannel_webhooks, sección 3.1) y referencia su id aquí.
    ];
}

/**
 * Despacho saliente — responde en el mismo hilo de WhatsApp vía Graph API.
 * `$accessToken`/`$phoneNumberId` son credenciales del canal (Fase 8.2),
 * nunca hardcodeadas. Falla de forma controlada: un error de envío no debe
 * propagarse como excepción no capturada hacia el webhook que lo originó
 * (Meta reintentará el evento entrante si el webhook responde con error,
 * pero el envío de salida es una operación aparte — su fallo se registra,
 * no se re-lanza).
 */
function sendWhatsAppReply(string $accessToken, string $phoneNumberId, string $toWaId, string $replyText): bool
{
    $url  = "https://graph.facebook.com/v19.0/{$phoneNumberId}/messages";
    $body = json_encode([
        'messaging_product' => 'whatsapp',
        'to'                => $toWaId,
        'type'              => 'text',
        'text'              => ['body' => $replyText],
    ], JSON_THROW_ON_ERROR);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ],
    ]);

    $response = curl_exec($ch);
    $errno    = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0 || $httpCode < 200 || $httpCode >= 300) {
        error_log('[whatsapp_webhook] Envío saliente falló (HTTP ' . $httpCode . ', curl ' . $errno . '): ' . (string) $response);
        return false;
    }

    return true;
}
```

**Degradación por credenciales no aprovisionadas:** si `$accessToken`/`$phoneNumberId` (u otro par de credenciales del canal) todavía no están configuradas cuando el webhook empieza a recibir tráfico real, el lado de recepción/normalización/persistencia debe seguir funcionando sin cambios — solo el envío de la respuesta se degrada a un no-op registrado en log. Un canal a medio aprovisionar no debe bloquear la captura de leads entrantes.

### 3.4 Worker CLI de Encolamiento Asíncrono (Fase 4)

Ejecutable exclusivamente vía CLI (`403` inmediato si se invoca por HTTP). Consume mensajes en `processing_status = 'queued'` de `omnichannel_messages`, invoca al motor central AURA y despacha la respuesta al canal de origen. Diseñado como fallback de hosting compartido sin daemon persistente (patrón ya validado en `extraer_aprendizaje.php` de este ecosistema); si el entorno cuenta con Redis, sustituir el `SELECT ... FOR UPDATE` por el consumidor de cola equivalente sin alterar el resto del flujo.

```php
#!/usr/bin/env php
<?php
declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Acceso denegado: este script solo se ejecuta vía CLI.');
}

/**
 * Worker de despacho asíncrono — omnichannel_worker.php
 * Uso: php omnichannel_worker.php (bajo cron o supervisor de procesos)
 */

require_once __DIR__ . '/conexion.php'; // Database::getConnection() — PDO blindado del proyecto

function processQueuedMessages(PDO $pdo): void
{
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            "SELECT id, tenant_id, session_id, ocmc_payload
             FROM omnichannel_messages
             WHERE processing_status = 'queued'
             ORDER BY created_at ASC
             LIMIT 10
             FOR UPDATE"
        );
        $stmt->execute();
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            $pdo->commit();
            return;
        }

        $ids = array_column($rows, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $lock = $pdo->prepare(
            "UPDATE omnichannel_messages SET processing_status = 'processing' WHERE id IN ($placeholders)"
        );
        $lock->execute($ids);
        $pdo->commit();
    } catch (\Throwable $e) {
        $pdo->rollBack();
        error_log('[omnichannel_worker] Error al reservar lote: ' . $e->getMessage());
        return;
    }

    foreach ($rows as $row) {
        dispatchToAuraAndRespond($pdo, $row);
    }
}

function dispatchToAuraAndRespond(PDO $pdo, array $row): void
{
    try {
        $ocmc = json_decode((string) $row['ocmc_payload'], true, 512, JSON_THROW_ON_ERROR);

        // Invocación al motor central AURA (AiOrchestrator) — dispatch síncrono
        // dentro del worker, nunca dentro del ciclo de vida del webhook HTTP.
        $auraResponse = AiOrchestrator::dispatch($row['tenant_id'], $ocmc);

        deliverToOriginChannel($ocmc['channel'] ?? '', $ocmc, $auraResponse);

        $update = $pdo->prepare(
            "UPDATE omnichannel_messages SET processing_status = 'delivered' WHERE id = :id"
        );
        $update->execute(['id' => $row['id']]);
    } catch (\Throwable $e) {
        error_log('[omnichannel_worker] Fallo procesando mensaje ' . $row['id'] . ': ' . $e->getMessage());

        $update = $pdo->prepare(
            "UPDATE omnichannel_messages SET processing_status = 'failed' WHERE id = :id"
        );
        $update->execute(['id' => $row['id']]);
    }
}

function deliverToOriginChannel(string $channel, array $ocmc, array $auraResponse): void
{
    // Despacho saliente específico por canal (API de envío de WhatsApp/Telegram,
    // o publicación al Widget Web vía tabla de polling/SSE). Implementación
    // delegada al normalizador de cada canal — ver Fase 3.
}

$pdo = Database::getConnection();
processQueuedMessages($pdo);
```

### 3.5 Clase de Referencia — Gestor de Enlaces Efímeros (Fase 7)

Artefacto agnóstico: sustituir el motor de persistencia (`PDO`/SQL aquí) por el equivalente del stack de destino sin alterar la lógica de conteo atómico, que es la pieza no negociable de este módulo.

```php
<?php
declare(strict_types=1);

/**
 * Gestor de tokens de acceso efímero — self-destruct tras N vistas.
 * El conteo atómico vive en una única sentencia UPDATE condicionada:
 * dos lecturas simultáneas nunca pueden ambas "ganar" la última vista.
 */
final class EphemeralAccessTokenManager
{
    public static function create(PDO $pdo, string $recursoRef, int $vistasMaximas): array
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        $stmt = $pdo->prepare(
            'INSERT INTO tokens_efimeros (token, recurso_ref, vistas_maximas)
             VALUES (:token, :recurso, :max)'
        );
        $stmt->execute(['token' => $token, 'recurso' => $recursoRef, 'max' => $vistasMaximas]);

        return ['token' => $token, 'id' => (int) $pdo->lastInsertId()];
    }

    /** Retorna el recurso solo si el enlace sigue activo y tenía una vista disponible; null en cualquier otro caso. */
    public static function redimir(PDO $pdo, string $token): ?array
    {
        $stmt = $pdo->prepare(
            "UPDATE tokens_efimeros
             SET vistas_actuales = vistas_actuales + 1,
                 estado = IF(vistas_actuales + 1 >= vistas_maximas, 'expirado', estado)
             WHERE token = :token AND estado = 'activo' AND vistas_actuales < vistas_maximas"
        );
        $stmt->execute(['token' => $token]);

        if ($stmt->rowCount() === 0) {
            return null; // No existe, ya expiró, o fue revocado manualmente.
        }

        $select = $pdo->prepare('SELECT * FROM tokens_efimeros WHERE token = :token LIMIT 1');
        $select->execute(['token' => $token]);
        return $select->fetch() ?: null;
    }
}
```

---

## 4. Guía de Consumo Agnóstico del Widget Web

El cliente final puede operar en cualquier stack tecnológico. El widget de conversación debe entregarse como un artefacto de integración mínima, sin asumir un framework:

- **Vanilla JS / HTML puro / WordPress:** entrega de un único `<script>` con carga asíncrona (`defer` o `async`), que inyecta su propio contenedor en el DOM y se comunica exclusivamente vía `fetch()` con el Gateway. No requiere build step ni dependencias del lado del cliente.
- **React / Vue / frameworks SPA:** el mismo script se monta como un componente wrapper ligero o, alternativamente, se expone un endpoint de datos consumible vía `fetch()`/`axios` para que el equipo del cliente construya su propia UI sobre el contrato OCMC de respuesta.
- **Contrato de comunicación:** el widget nunca conoce las llaves de inferencia ni el `.md` de conocimiento — solo envía el mensaje del usuario y un identificador de sesión, y recibe la respuesta ya procesada. Esto mantiene el aislamiento de IP (sección 1.1) independientemente del stack elegido.
- **Requisito mínimo de hosting del cliente:** capacidad de servir un archivo estático (el script del widget) y, si el puente proxy vive en el mismo servidor, soporte PHP 8.x con cURL habilitado. No se requiere Node.js, contenedores, ni infraestructura adicional del lado del cliente.

---

## 5. Módulo de Enlaces Efímeros y Protección de Información Exclusiva (Self-Destruct Link Architecture)

> Diseño 100% agnóstico de stack, lenguaje o marca. Aplica a cualquier proyecto que necesite compartir información privada (una propuesta comercial, un itinerario, un documento de precios) mediante un enlace que no debe circular indefinidamente.

### 5.1 Principio rector

Un enlace que apunta a información exclusiva no es seguro solo por ser difícil de adivinar — también debe morir por sí mismo. La protección real no depende de que el destinatario original "no lo comparta"; depende de que, si lo comparte, el enlace deje de funcionar después de un número acotado de aperturas, sin intervención manual del propietario.

### 5.2 Contrato del Token Efímero

Cada enlace generado se resuelve a un token de acceso único, opaco, con entropía criptográfica suficiente para no ser adivinable ni enumerable. El token no codifica información del recurso protegido — es una referencia indirecta, resuelta del lado del servidor.

```json
{
  "token": "identificador_opaco_url_safe",
  "recurso_ref": "referencia_interna_al_contenido_protegido",
  "vistas_maximas": "entero_positivo_configurable",
  "vistas_actuales": "entero_no_negativo",
  "estado": "activo | expirado | revocado"
}
```

### 5.3 Lógica de Caducidad por Conteo Atómico de Accesos

La regla no negociable de este módulo: el incremento del contador de vistas y la verificación de si aún queda cupo deben ocurrir como **una sola operación atómica** contra el almacén de persistencia, nunca como una lectura seguida de una escritura separada. Cualquier separación entre "leer cuántas vistas quedan" y "registrar una vista más" abre una ventana de condición de carrera: dos aperturas simultáneas del mismo enlace, en el instante en que solo queda una vista disponible, podrían ambas leer "queda una" y ambas escribir "consumida", entregando el contenido protegido dos veces cuando el diseño exige entregarlo una sola.

La forma correcta es condicionar la propia operación de escritura a que aún exista cupo (`incrementar el contador SOLO SI vistas_actuales < vistas_maximas`, evaluado por el motor de persistencia como una unidad indivisible). Quien "gana" la última vista es quien la escritura atómica confirma primero; el resto recibe la señal de que el enlace ya no tiene cupo, sin haber consumido nada.

### 5.4 Estados y Transiciones

- **Activo** → estado inicial al generarse el token, mientras `vistas_actuales < vistas_maximas`.
- **Expirado** → transición automática, disparada por la misma operación atómica de conteo, en el momento en que la vista que se está registrando alcanza el máximo configurado.
- **Revocado** → transición manual, disparada por el propietario del recurso desde su panel de control, independiente del conteo de vistas — permite matar un enlace antes de que se agote naturalmente (ej. el cliente ya no debe verlo).

Ningún estado retrocede: un enlace expirado o revocado no vuelve a "activo" — se genera uno nuevo si se necesita compartir el recurso de nuevo.

### 5.5 Integración al Panel de Control del Propietario

- El propietario del proyecto debe poder generar un nuevo enlace efímero desde su panel de administración, indicando el recurso a proteger y, opcionalmente, un límite de vistas distinto al valor por defecto del sistema.
- El panel debe exponer un valor global por defecto de "vistas antes de autodestrucción" (recomendado: 3), editable sin requerir cambios de código ni migraciones — un ajuste de configuración persistido, no una constante de código fuente.
- El panel debe listar los enlaces activos junto con su conteo de vistas consumidas/máximas, y permitir tanto editar el límite de un enlace individual (sin poder bajarlo por debajo de las vistas ya consumidas) como revocarlo manualmente antes de que expire por conteo.
- Ningún estado interno de este módulo (tokens, contadores, recurso protegido) debe filtrarse en un mensaje de error hacia el visitante final — un enlace inválido, expirado o revocado se resuelve siempre a una página de cortesía neutra, nunca a un error técnico crudo de la capa de persistencia.

### 5.6 Criterios de Aceptación (agnósticos)

- [ ] Dos solicitudes concurrentes contra la última vista disponible de un enlace nunca resultan en que ambas reciban el contenido protegido.
- [ ] El límite global de vistas por defecto es editable en caliente desde el panel de control, sin despliegue de código.
- [ ] Un enlace revocado manualmente deja de servir contenido de inmediato, sin importar cuántas vistas le quedaran.
- [ ] Ningún error de base de datos o de resolución de token se muestra crudo al visitante final.

### 5.7 Extensión Opcional — Plantillas de Contenido Reutilizable

Cuando el recurso protegido es contenido estructurado y repetitivo (una tarifa de servicio, un paquete, un nivel de precio) en lugar de un documento único por cliente, el panel de control puede exponer un catálogo de plantillas predefinidas que precargan el campo de contenido antes de generar el token — el operador humano elige una plantilla, ajusta lo específico del cliente si aplica, y genera el enlace normalmente. Esto no cambia nada del contrato del token (sección 5.2) ni de la lógica de conteo atómico (sección 5.3); es puramente una conveniencia de captura de datos en el panel.

Dos requisitos no negociables si se implementa esta extensión:
- **Saneamiento de contenido más permisivo, no menos seguro:** si las plantillas incluyen el patrón de bilingüismo de par de nodos (ver Regla de Oro del proyecto que consuma este molde), el sanitizador del lado servidor debe permitir explícitamente el elemento contenedor que transporta el atributo de idioma — omitirlo de la lista blanca aplana ambos idiomas en un solo bloque de texto y rompe el contrato bilingüe en tiempo de render, no en tiempo de guardado (el error es silencioso hasta que alguien abre el enlace).
- **Ninguna plantilla vive en este documento agnóstico:** el contenido real de cualquier plantilla (tarifas, nombres de rutas/paquetes, condiciones comerciales) es propiedad exclusiva del proyecto concreto que la usa y se registra en el Codex de ese proyecto — nunca aquí (Mandato de Sincronización Génesis, ver sección 6).

---

## 6. Notas de Gobernanza

- Este documento vive en `knowledge/Santuario_Genesis/modulos/` como **molde agnóstico** — ningún dato real de un cliente o tenant específico debe incorporarse aquí (Mandato de Sincronización Génesis, Ley de Fricción Cero).
- Cualquier mejora futura a este módulo debe seguir el Protocolo de Actualización Secuencial en Cascada: primero estabilización operativa real, después propagación genérica al Santuario, después compilación monolítica del proyecto que lo consuma.
- Los nombres de tablas y campos aquí propuestos (`omnichannel_*`, `tokens_efimeros`) son una referencia de diseño — su creación en un proyecto concreto requiere validación contra el Codex vigente de ese proyecto y autorización explícita antes de cualquier `CREATE TABLE`.
- El Módulo de Enlaces Efímeros (sección 5 / Fase 7) fue estabilizado por primera vez en un proyecto concreto antes de propagarse aquí como molde — la implementación real usó `ll_ephemeral_links` como nombre de tabla y quedó registrada en el Codex de ese proyecto, no en este documento agnóstico.
- (2026-07-29) El esquema de la sección 3.1 y la lógica de conteo atómico de la sección 5.3/3.5 quedaron confirmados en despliegue real de producción (creación de tabla exitosa, verificación de TLS del dominio del proyecto concreto). La extensión de plantillas de contenido (sección 5.7) nace de esa misma estabilización real. Cualquier nombre comercial que el proyecto concreto le haya dado a su implementación de este módulo vive únicamente en el Codex de ese proyecto — nunca en este molde.
- (2026-08-04, v1.3) Añadidas la sección 1.4 (Arquitectura de la Fuente de Verdad — patrón de tres capas: Prompt Maestro + Catálogo Estructurado en BD + Reglas/FAQs), la sección 1.5 (Fallback Cognitivo Multi-Proveedor opcional, con la directriz de tratarlo como "Fase 2/Pausada" hasta validar el canal directo), la Fase 8 (Panel de Administración Super Admin/Cockpit — separación de roles, credenciales de canal, gestor de plantillas, CRUD visual de catálogo, visor de leads) y el round-trip completo de WhatsApp en la sección 3.3 (normalización a OCMC + despacho saliente vía Graph API, antes solo se documentaba el lado entrante). Estas cinco adiciones generalizan hallazgos y requisitos reales de un proyecto concreto que ya los tenía implementados — ningún nombre de tabla, endpoint, ni dato de negocio específico de ese proyecto se incorporó aquí (Mandato de Sincronización Génesis); esos detalles concretos viven en el Codex de ese proyecto. La nota de concurrencia al final de la Fase 8 generaliza un hallazgo real de condición de carrera en un mecanismo anti-CSRF de sesión compartida — mismo principio, cero detalle de implementación específico.
