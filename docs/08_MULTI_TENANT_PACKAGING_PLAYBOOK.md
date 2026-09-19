# 🧬 08 — PAQUETE GENÉRICO "CONCIERGE IA" PARA MÚLTIPLES CLIENTES

> **Clasificación:** Documento operativo de planeación — creado 2026-09-19 a petición explícita del Arquitecto ("que este sistema de chatbot lo tenga 10 clientes y solo meter sus configuraciones y sus prompts y listo").
> **Estado:** Plan aprobado en su enfoque (Opción A — una instalación por cliente), **nada de la Fase 2 en adelante está construido todavía**. Este documento es la preparación — el inventario, el diseño de la página de configuración, y la guía para ejecutarlo — no el resultado.
> **Alcance:** cubre solo el módulo "Concierge IA" (chatbot omnicanal + captura de leads + cotizaciones). El resto del Cockpit (Book Editor, Work Report, Payments/Alianzas — todo lo que es específico del libro/negocio de Lester) **no** es parte de este paquete genérico; se queda en el proyecto de Lover Lips Yachts exclusivamente.

---

## 1. Contexto y decisión ya tomada

El Arquitecto confirmó **Opción A**: cada cliente nuevo es una **copia independiente** de este mismo repositorio (código + su propia base de datos), no un sistema multi-tenant compartido. Ventajas ya validadas en la conversación que originó este documento:
- Un cliente nunca puede ver datos de otro por accidente (aislamiento total, no depende de que ningún filtro `tenant_id` esté bien puesto en cada query).
- El patrón "cópialo y pégalo" ya es exactamente como se documenta el conocimiento reusable de este proyecto — `modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md` (el "molde" agnóstico) ya es la versión sin nombres de Lover Lips Yachts de la arquitectura completa. Este documento es el paso siguiente: convertir ese molde conceptual en un **paquete de archivos reales** + una **página de configuración real**.
- El diseño omnicanal (OCMC, sección 1.3 del molde) ya es agnóstico de canal por construcción — el mismo Gateway Central sirve al widget web, WhatsApp, y cualquier canal futuro (Telegram, redes sociales) sin cambios de arquitectura, solo un adaptador nuevo por canal (ver hallazgo del Bug #7, ronda 4 de QA, sobre por qué cada adaptador debe invocar el mismo post-procesamiento).

---

## 2. Inventario real de archivos — 3 categorías

Basado en grep real contra el código (no supuesto) el 2026-09-19. La mayoría de los archivos con "Lover Lips"/"Lester" en el conteo de abajo **solo lo tienen en el comentario de encabezado** (`LOVER LIPS YACHTS — nombre_archivo.php`, convención de todo el repo) — eso es cosmético, no bloquea nada. Lo que sí bloquea es cuando el nombre de marca aparece en **lógica real o copy visible al usuario**.

### 2.A — Genéricos tal cual (copiar sin tocar, cero cambios de lógica)
Motor de infraestructura — funciona igual para cualquier negocio de hospitalidad/reservas:

| Archivo | Por qué es genérico |
|---|---|
| `core/EphemeralLinkManager.php` | Mecanismo de enlace autodestructible — sin ningún dato de negocio |
| `core/OmnichannelRepository.php` | Persistencia OCMC — tablas/columnas ya agnósticas de canal y de negocio |
| `core/AuraSatelliteClient.php` / `core/OpenAiFallbackClient.php` | Clientes de proveedor de IA — configurables por variables de entorno |
| `core/EnvSettingsStore.php` | Almacén de configuración genérico (lista blanca de llaves, ya reusable) |
| `core/auth_check.php` / `core/dev_bypass.php` | Sesión/autenticación — sin dato de negocio (dev_bypass tiene un email de prueba trivial a cambiar) |
| `api/conexion.php` | Conexión a BD local-vs-producción — solo necesita las credenciales del cliente nuevo en su propio `core/.env` |
| `api/public/l.php` | Página de cotización — genérica, lee `core/pgai_templates.php` (ver 2.B) |
| `api/public/whatsapp_webhook.php`, `api/public/ai_widget_gateway.php` | Adaptadores de canal — genéricos, **deben seguir llamando ambos exactamente los mismos pasos de post-procesamiento** (ver Bug #7) |
| `assets/js/pg_ai_widget.js`, patrón de `chat-lab.php` | Widget de chat — sin copy de negocio hardcodeado |
| Patrón CRUD de `core/FleetCatalogRepository.php` / `CrewRepository.php` / `InventoryCatalogRepository.php` + sus páginas en `pg_ai_config.php`/`checklist.php` | La **forma** (whitelist de columnas, CSRF, sesión) es 100% reusable — los **nombres de columna** (`vessel_name`, rutas tipo "Balandra") son específicos de yates; un negocio de otro giro (ej. un spa, un hotel) reusaría el patrón pero renombraría las columnas |

### 2.B — Necesitan volverse configurables (hoy hardcodeados, hay que extraerlos)
Esto es el trabajo real de este proyecto — mover estos 5 puntos de un archivo fijo a la Página de Configuración (sección 4):

| # | Qué | Dónde vive hoy | A dónde debe moverse |
|---|---|---|---|
| 1 | **Persona/prompt maestro completo** | `core/prompts/pg_ai_lester_master.md` (texto fijo: nombre del negocio, historia del dueño, tono, reglas de negocio) | Campo de texto grande en la Página de Configuración — el molde ya define las variables `{{business_name}}`/`{{knowledge_base}}`/`{{local_date_time}}` (sección 2.5 del molde) que nunca se llegaron a usar de verdad en este archivo — hay que sustituirlas en tiempo real, no a mano |
| 2 | **Plantillas de cotización** (`rate_mxn`, inclusiones, políticas, nombres de ruta) | `core/pgai_templates.php` — función que retorna un array fijo con "balandra"/"espiritu_santo" | Tabla en BD (mismo patrón que `ll_fleet_catalog`) + editor CRUD en la Página de Configuración — cada cliente tiene sus propias rutas/experiencias/precios |
| 3 | **Palabras clave de ruta para extracción determinística** | `core/PgAiActionProcessor.php::ROUTE_KEYWORDS` (Balandra, Espíritu Santo, Maranatha, Pink Lips — hardcoded) | Debe generarse dinámicamente desde la misma tabla del punto 2 — nunca una segunda lista a mano que se puede desincronizar de las plantillas reales |
| 4 | **Identidad de marca** (nombre del negocio, número de WhatsApp, colores) | Repartido en `dashboard.php`/`pg_ai_hub.php`/`pg_ai_config.php` (texto) + `assets/css/style.css` (tokens `--pink`/`--gold`) + `core/.env` (número de WhatsApp) | Un solo lugar: la tabla `ll_app_settings` ya existente (mismo mecanismo que `ephemeral_link_default_max_views`) + tokens CSS leídos de ahí en vez de fijos |
| 5 | **`tenant_id` fijo** | `core/ProxyBridge.php` (valor tipo `'loverlipsyachts-dev'` fijo) | Una constante en `core/.env` del cliente — no cambia el diseño de tabla (`tenant_id` ya existe como columna), solo de dónde se lee el valor |

### 2.C — Nunca se copian (datos reales de Lover Lips Yachts, se quedan fuera)
- Todo el contenido de `ll_fleet_catalog`/`ll_crew_members`/`ll_inventory_catalog` (los 42 yates reales, la tripulación real)
- `knowledge/Lover_Lips_Yachts_FUENTEDEVERDAD_CONSOLIDADA.md` y el resto de `knowledge/`
- `core/.env` (credenciales reales — cada cliente genera las suyas desde cero)
- Todo lo que NO es "Concierge IA": `book.php`/`book_editor.php`/`invitation.php` (el libro de Lester), `strategy.php`/`alianzas.php` (el modelo de sociedad 50/50 específico de este cliente), `propuestas.php`, `reportes.php`

---

## 3. Esquema propuesto del "archivo de configuración de cliente"

En vez de un archivo plano (`.json`/`.env` adicional), se recomienda una **fila en `ll_app_settings`** por cada llave — así se edita desde la Página de Configuración sin tocar archivos ni redesplegar, exactamente como ya funciona `ephemeral_link_default_max_views`. Llaves propuestas (prefijo `client_` para no chocar con settings ya existentes):

```
client_business_name          → "Lover Lips Yachts"
client_owner_persona_name     → "Lester Keizer"
client_owner_bio_short        → texto libre, 2-3 líneas para el prompt
client_whatsapp_number        → "+1 702 204 8894"
client_brand_color_primary    → "#E91E63" (hex, alimenta --pink)
client_brand_color_secondary  → "#D4AF37" (hex, alimenta --gold)
client_domain                 → "loverlipsyachts.com"
client_tenant_id              → "loverlipsyachts-dev"
client_master_prompt          → el prompt completo (reemplaza al archivo .md fijo)
```

Las **rutas/experiencias/precios** (punto 2.B #2) no caben en una fila de texto — usan su propia tabla nueva, ver Fase 2 del TODO.

---

## 4. Página de Configuración — plan concreto (no construida todavía)

**No es una página nueva desde cero** — se extiende `pg_ai_config.php`, que ya tiene exactamente el patrón necesario (formulario + `EnvSettingsStore`, sección "🔐 Credentials Vault"). Se agrega una **Sección 0 — Identidad del Cliente**, antes de la Sección 1 (Fleet Catalog), visible para `owner` (no solo `super_admin`, porque es lo primero que alguien nuevo llenaría al recibir su copia del sistema):

- Campos de texto: nombre del negocio, nombre/bio del dueño, WhatsApp, dominio.
- 2 selectores de color (o campos hex) para los tokens de marca.
- Un `<textarea>` grande para el prompt maestro — mismo componente ya construido para el "📜 Master Prompt Editor" (Sección 2 actual), solo que ahora también alimentado por variables de negocio en vez de ser 100% texto fijo.
- Una tabla CRUD nueva "Rutas y Cotizaciones" — mismo patrón exacto que el Fleet Catalog Editor (formulario arriba + tabla con ✏️/✕), reemplazando `core/pgai_templates.php`.

---

## 5. TODO LIST (secuencial, cada fase depende de la anterior)

- [ ] **Fase 0 — Preparación (este documento).** Inventario de archivos, diseño del esquema de configuración, plan de la página. **Completada 2026-09-19.**
- [ ] **Fase 1 — Tabla de rutas/cotizaciones.** Nueva migración `sql/016_create_ll_quote_routes.sql` (id, route_slug, title_en/es, rate_amount, rate_currency, description_en/es, inclusions JSON, policies JSON, display_order). Repositorio `core/QuoteRouteRepository.php` (mismo patrón que `FleetCatalogRepository`).
- [ ] **Fase 2 — `core/pgai_templates.php` lee de la tabla, no del array fijo.** `lly_pgai_quote_templates()` pasa a construirse desde `QuoteRouteRepository::listAll()` — cero cambios en quién la consume (`PgAiActionProcessor::resolveQuoteLink()`, `api/public/l.php`).
- [ ] **Fase 3 — `ROUTE_KEYWORDS` se genera desde la misma tabla.** `PgAiActionProcessor::extractRoute()` deja de tener la lista fija — la arma en tiempo de ejecución (con cache simple) desde `QuoteRouteRepository`.
- [ ] **Fase 4 — Llaves de identidad de marca en `ll_app_settings`.** Agregar las llaves de la sección 3 de este documento (`client_*`) a la lista blanca de `EnvSettingsStore`.
- [ ] **Fase 5 — Prompt maestro dinámico.** `core/ProxyBridge.php` deja de leer `core/prompts/pg_ai_lester_master.md` como archivo fijo — lee `client_master_prompt` de settings y sustituye `{{business_name}}`/`{{knowledge_base}}`/`{{local_date_time}}` en tiempo real (el molde ya definía esto en la sección 2.5, nunca se conectó). El archivo `.md` se conserva como plantilla de referencia/respaldo, no como fuente en vivo.
- [ ] **Fase 6 — `client_tenant_id` reemplaza el valor fijo en `ProxyBridge`.**
- [ ] **Fase 7 — Tokens de color CSS leen de settings.** `assets/css/style.css` mantiene sus variables `--pink`/`--gold` con los valores actuales como default — una pequeña inyección de `<style>` inline (mismo patrón ya usado en el fix del hero de WordPress) sobreescribe con los valores de `client_brand_color_*` si están configurados.
- [ ] **Fase 8 — Página de Configuración (Sección 0 de `pg_ai_config.php`).** Construir el formulario descrito en la sección 4 de este documento, con su endpoint `api/client_config.php` (mismo pipeline de 6 capas que `api/fleet_catalog.php`).
- [ ] **Fase 9 — Prueba real de un "cliente ficticio".** Clonar el repo a una carpeta de prueba, base de datos nueva, llenar la Página de Configuración con datos inventados de un negocio distinto (ej. un spa de playa ficticio, nunca datos reales de un cliente real sin autorización), correr la batería completa de pruebas del chatbot (mismo patrón de las 4 rondas de QA de este mismo día) contra esa instancia — confirmar que responde con la identidad nueva, no con "Lover Lips"/"Lester" en ningún lado.
- [ ] **Fase 10 — Documentar el runbook de onboarding real** (sección 6 de este documento) con cualquier ajuste que la Fase 9 revele.

## 6. CHECKLIST de aceptación (para considerar el paquete "listo para el cliente #2")

- [ ] Ningún archivo de `core/` o `api/` tiene el string literal "Lover Lips" ni "Lester" en lógica ejecutable (los comentarios de encabezado `LOVER LIPS YACHTS — archivo.php` se aceptan como cosméticos, se reemplazan con buscar-y-reemplazar al clonar, no bloquean).
- [ ] El chatbot responde con el nombre de negocio/persona configurados, nunca "Lover Lips"/"Lester", para una instancia de prueba con datos ficticios distintos.
- [ ] Cambiar cualquier campo de la Sección 0 (Página de Configuración) tiene efecto inmediato en la siguiente respuesta del chatbot, sin necesidad de tocar código ni redesplegar.
- [ ] Las 4 rondas de pruebas ya documentadas hoy (idioma, extracción de lead, escalación, enlaces efímeros, WhatsApp, concurrencia) se repiten sin fallas nuevas contra la instancia de prueba.
- [ ] `sql/016` y cualquier migración nueva sigue la misma disciplina del proyecto (escrita, nunca corrida automáticamente, un humano la corre a mano).

---

## 7. Guion de "onboarding" para un Claude nuevo (para cuando el paquete esté completo)

> Instrucciones que se le pueden pegar a una sesión de Claude sin ningún contexto previo, una vez que las Fases 1-8 de la sección 5 ya estén construidas y verificadas (Fase 9/10). **Hoy (2026-09-19) esto todavía no aplica** — es la meta, no el estado actual.

1. Lee `modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md` completo primero — es el contrato agnóstico que este paquete implementa. No inventes ningún campo/tabla/regla que no esté ahí o en este documento (`docs/08_...`).
2. Clona este repositorio a la carpeta del cliente nuevo. Crea una base de datos nueva y vacía para él — nunca reutilices la de otro cliente.
3. Corre, en orden, todas las migraciones `sql/*.sql` que empiecen server-side listadas en este repo (manualmente, vía phpMyAdmin/cPanel — nunca automático, ver `docs/02_SYSTEM_CODEX_REGISTRY.md` para la razón).
4. Copia `core/.env.example` a `core/.env` y llena las credenciales REALES del cliente nuevo (su propia BD, su propio número de WhatsApp Business si aplica, su propia llave de OpenAI/AURA). Nunca reuses una credencial de otro cliente.
5. Entra a `pg_ai_config.php` → Sección 0 (Identidad del Cliente) → llena nombre del negocio, persona del dueño, WhatsApp, colores, y el prompt maestro del cliente (usa la plantilla portable de `modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md` sección 2 como punto de partida, sustituye los marcadores `{{...}}` con los datos reales de este cliente).
6. En la nueva tabla de Rutas/Cotizaciones, da de alta sus experiencias/precios reales — nunca inventes un precio o inclusión que el cliente no haya confirmado (Mandamiento 4 — Anti-Alucinación, aplica igual aquí).
7. Corre la batería de pruebas de `chat-lab.php` (idioma, captura de lead, escalación si aplica a este negocio, límite de vistas del enlace, WhatsApp si está provisionado, carga simultánea) — el mismo patrón exacto documentado en `docs/02_SYSTEM_CODEX_REGISTRY.md`, hitos "Ronda 1-4" del 2026-09-18/19 de Lover Lips Yachts — como ejemplo de CÓMO probar, nunca copies sus datos de prueba literalmente.
8. Documenta cualquier hallazgo nuevo en el Codex propio de ESE cliente (nunca en el de Lover Lips Yachts) — y si el hallazgo es genérico (no específico de ese negocio), generalízalo de vuelta a `modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md`, siguiendo la misma disciplina de "sin nombres propios" que todas las entradas de changelog de ese archivo ya siguen.
