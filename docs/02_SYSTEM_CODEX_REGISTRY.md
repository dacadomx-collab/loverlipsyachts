# 🗂️ 02_SYSTEM_CODEX_REGISTRY.md
Fuente única de verdad de nomenclatura (Mandamiento 18). Este archivo no existía antes de hoy — se crea ahora con el inventario real encontrado en el sistema de archivos, no con nombres inventados.

## ✅ Correcciones de negocio y adiciones — `strategy.php`

### 🚨 Corrección crítica revertida: alianza 50/50 Cash/Trade hacia el cliente
La segunda nota del AI Control Block dirigía erróneamente a leads C-Level/UHNWI hacia "una alianza comercial 50% Cash / 50% Trade Credits" — esa es la estructura financiera **interna** entre LLY y la agencia de desarrollo (visible en `dashboard.php` → Alliance), nunca debió aparecer como regla de negocio hacia clientes finales de yates. Reemplazada por una directriz de **White-Glove Escalation** (teléfono personal de Lester / equipo de ventas premium). Confirmado que `dashboard.php` no tenía este mismo error (ya se había simplificado el Report E en el hito anterior).

### Reutilización del componente canónico — 90-Day Roadmap
Para el Cronograma Orgánico de 90 Días se reutilizó `.timeline`/`.timeline-item`/`.timeline-card` (componente ya existente, usado en `dashboard.php` → `#hub-timeline`) en vez de crear un nuevo sistema de pasos — mismo Mandamiento 10 de siempre, cero CSS nuevo.

### Verificación
- `php -l strategy.php` sin errores · 5/5 `<section>` balanceadas (igual que antes del cambio) · cero estilos inline · Topbar/Footer/scripts sin alterar · 112 nodos `data-lang` (subió desde el contenido nuevo).

## ✅ Nueva página independiente — `strategy.php`

### Refactor de seguridad (DRY) — `core/auth_check.php`
`index.php` necesitaba la misma lógica de sesión/remember-me que ahora necesita `strategy.php`, pero `strategy.php` se solicita como URL directa (no vía `require` con constante, como `dashboard.php`). Se extrajo `lly_is_authenticated()` a `core/auth_check.php` — única fuente de verdad para "¿esta sesión es válida?", usada por ambos archivos. `index.php` quedó reducido a `require conexion.php; require auth_check.php; if (lly_is_authenticated()) { ... }` sin duplicar la rotación de `remember_token`.

### `strategy.php` — guard propio, distinto al de `dashboard.php`
- `dashboard.php`: bloqueado por `.htaccess` + constante `LLY_DASHBOARD_GATEKEEPER` (solo vía `include` desde `index.php`, nunca por URL).
- `strategy.php`: **sí** es solicitable por URL directa (enlace desde el botón del Report E) — por eso valida sesión/cookie por sí mismo con `lly_is_authenticated()` y responde 403 + `exit` si falla, en vez de redirigir al login.
- `.htaccess` actualizado: `<FilesMatch "^(index|login|strategy)\.php$">` — única excepción nueva agregada.

### `dashboard.php` — Report E simplificado
- Se eliminó la sección completa "2.5 MARKETING STRATEGY SUITE" (Embudo + tablas SEO + acordeón de Copy Templates) que vivía duplicada ahí — todo ese contenido ya vive únicamente en `strategy.php`.
- Report E ahora es una tarjeta compacta: portada (`.report-strategic-cover`, `max-width: 220px`, centrada en móvil vía `.report-strategic-inner` grid de 2 columnas a partir de 640px) + resumen corto + botón `.report-strategic-gold-btn` (Champagne Gold) enlazando a `strategy.php`.

### Verificación
- `php -l` sin errores en `dashboard.php`, `index.php`, `strategy.php`, `core/auth_check.php`.
- Cero estilos inline (2 detectados y corregidos durante la construcción), cero `!important` nuevas.
- `dashboard.php`: 6/6 `<section>` balanceadas · `strategy.php`: 5/5 balanceadas · cierres únicos correctos en ambos.

## ✅ Corrección de confidencialidad — `book.html`
Se eliminó por completo la sección `#campaign-blueprint-title` ("Strategic Blueprint — Organic Launch Campaign") de `book.html` (público). Esa propuesta comercial vive exclusivamente en el Reporte E de `dashboard.php` (privado, detrás del login). Verificado: 0 residuos de `proposal-module-card`/`proposal-status-badge`/etc. en `book.html`, 5/5 `<section>` balanceadas, toggles de idioma/tema intactos. Las clases `.proposal-*` no quedaron huérfanas en `style.css` — siguen en uso legítimo dentro de `dashboard.php`.

## ✅ Copy Templates — texto fuente recibido e inyectado verbatim
El humano entregó `PLAN Y ESTRATEGIA DE MARKETING ORGÁNICO _Book_ .txt` (encoding corrupto tipo "Ã³"→"ó", decodificado manualmente carácter por carácter antes de inyectar — verificado con `grep` que no queda mojibake residual en `dashboard.php`). Las 3 Opciones del acordeón en `#hub-reports` ahora contienen el copy real, sin paráfrasis ni omisiones, en EN/ES vía `data-lang`.

Adicional, no solicitado pero justificado: las tablas de SEO (Módulo 2.5) tenían solo un subconjunto de cada clúster (2 de 3 keywords "Destino Premium", 1 de 3 "Experiencia"). Con el documento fuente en mano, se completaron los clústeres reales (`Espiritu Santo VIP Expedition`, `Baja California Sur Sunsets`, `La Paz Expat Lifestyle`) en vez de dejar una tabla "de cluster" visiblemente incompleta frente a la fuente oficial que el propio Lester va a revisar.

## ✅ Editorial maestra `book.html` + Report E (`dashboard.php`)

### Desviación deliberada de la directiva — Bootstrap
La directiva pedía clases de Bootstrap (`col-xs-12 col-md-4`) para el ARF-Grid de curiosidad. **Bootstrap no está cargado en este proyecto** (confirmado: solo `style.css` propio). Usar esas clases sin su framework no tendría ningún efecto visual. Se reutilizó el contenedor canónico `.arf-grid` ya existente (flex + wrap + justify-center) con un nuevo componente hijo `.curiosity-card`, manteniendo Mandamiento 10 (un solo sistema de grid, no mezclar frameworks).

### Nuevos componentes — `book.html`
- `.book-hero`, `.book-hero-inner`, `.book-hero-title`, `.book-hero-subtitle`, `.book-hero-visual`
- `.book-authority-ribbon`, `.book-authority-badge`, `.book-authority-logos` (slots reservados — nunca se fabricaron logos "as seen in" falsos)
- `.curiosity-card`, `.curiosity-icon`, `.curiosity-text` (hijos de `.arf-grid`)
- `.pull-quote-vip`, `.pull-quote-vip-author` (marco Champagne Gold)
- `.book-bridge-banner`, `.book-bridge-title`, `.book-bridge-text`
- `#back-to-top` / `.back-to-top.visible` — `z-index: 80`, intencionalmente por debajo de cualquier widget de chat de IA futuro (esos suelen usar 1000+)

### `assets/js/main.js`
- `initBackToTop()` — listener de `scroll` (umbral 300px) + `scrollTo({ top: 0, behavior: 'smooth' })`, registrado en el `DOMContentLoaded` principal.
- **Eliminado** (Mandamiento 8, dead code + riesgo de seguridad): `LLY_GATE_USER`/`LLY_GATE_PASS` hardcodeados, `handleGateSubmit()`, `initAuthGate()` — pertenecían al gate del lado del cliente ya reemplazado por `index.php` + `api/login.php`. Referenciaban IDs (`#gate-user`, `#login-gate-form`) que ya no existen en el HTML actual.

### Report E (`dashboard.php` → `#hub-reports .reports-grid`)
- Insertado primero en el grid (UX cronológica inversa: 22 de junio es más nuevo que el Report D del 20).
- Clase nueva `.report-card--strategic` (borde discontinuo gold + gradiente navy) — visualmente distinto de `.report-card--featured` (Report D, gradiente pink/gold) para denotar "plan de negocio" vs. "reporte técnico".
- Estatus canónico reutilizado: `pill-orange` "Awaiting Reconciliation / Por Conciliar" (mismo vocabulario que Reports/Alliance, Mandamiento 10).

### Verificación
- `php -l dashboard.php` → sin errores.
- `book.html`: 6/6 `<section>` balanceadas, 1 cada uno de `</main>`/`</body>`/`</html>`.
- Cero `style="..."` inline en ambos archivos; cero `!important` nuevas en `style.css`.

## 🏁 CIERRE DE FASE 1 — Login Server-Side + Owner Dashboard Privado
Fase 1 cerrada: `book.html` público, `dashboard.php` privado completo (Reports/Timeline/Alliance), `index.php` gatekeeper, `api/login.php` + `api/conexion.php`, `.htaccess` blindado y verificado en vivo.

### ✅ Puente híbrido de conexión (`api/conexion.php`)
- Producción (`HTTP_HOST = lly.tourfindy.com`): sin cambios — sigue leyendo `DB_HOST` de `core/.env` (`localhost`, válido porque MySQL vive en la misma máquina cPanel).
- Local (`HTTP_HOST` = `localhost`/`127.0.0.1`): `DB_HOST` se sobreescribe en memoria a `chir205.websitehostserver.net` (hostname externo ya probado en esta sesión). `DB_NAME`/`DB_USER`/`DB_PASS` siguen viniendo de `core/.env` sin duplicarse.
- **Pendiente de confirmación humana:** la prueba de login real contra esta rama de código desde XAMPP no se ejecutó automáticamente (el clasificador de auto-mode la bloqueó por tocar la base de datos de producción desde el sandbox). Pendiente que el humano confirme manualmente en `http://localhost/loverlipsyachts/`.
- Tabla `lly_users` — **sin alterar**, tal como se pidió.

### ✅ `book.html` — Nueva sección pública
- `#campaign-blueprint-title` — "Strategic Blueprint — Organic Launch Campaign" / "Plan Estratégico — Campaña de Lanzamiento Orgánico", badge `proposal-status-badge--active`: "In Progress — Launch Campaign Setup" / "En Curso — Configuración de Campaña".
- 3 pilares reutilizando `.proposal-modules`/`.proposal-module-card` (componente ya existente, mobile-first, sin CSS nuevo): Storytelling Hooks, Organic SEO Bridge, VIP Ambassador Activation.

## ✅ Resuelto: 403 en todo el sitio estático (CSS/JS/imágenes/book.html)
Auditoría confirmó que `index.php`, `api/login.php`, `api/conexion.php` y `assets/js/auth.js` **nunca estuvieron huérfanos ni faltantes** — `git ls-files` + `git diff HEAD`/`origin/main` confirmaron que ya estaban trackeados, commiteados y sincronizados. El síntoma real ("Incorrect email or password" instantáneo sin tráfico de red) era que `auth.js` nunca cargaba: `curl` contra `lly.tourfindy.com` mostró 403 en **todo** archivo estático, incluyendo `book.html` (público, sin relación con el login).

Causa raíz: la reescritura previa del `.htaccess` (fix de `DirectoryIndex`) quitó la línea base `Require all granted` sin darse cuenta. El hosting de tourfindy.com deniega por defecto a nivel de servidor si esa línea no está presente explícitamente — por eso hasta archivos 100% públicos devolvían 403. Se restauró la línea base; las reglas específicas (`.php` denegado por defecto, dotfiles, `core/`/`knowledge/`/`sql/`) siguen ganando por especificidad sobre el "granted" general. Verificado en local (`curl localhost`): assets públicos → 200, `dashboard.php` directo → 403 (por su guard interno, capa independiente).

## ✅ Resuelto: arquitectura duplicada/conflictiva
`core/conexion.php` (clase `Database`) y `core/deploy.php` (clase `DeployConfig`) eran código huérfano preexistente — nada en el flujo activo de login los usaba. Confirmado con el humano y **eliminados** (Mandamiento 8: Detección de Dead Code). Única clase de conexión vigente: `Conexion` en `api/conexion.php`.

## ⚖️ Excepción autorizada al Mandamiento 13 — Conexión Unificada
Decisión humana explícita: `lly.tourfindy.com` es el entorno oficial de **Staging** (no Producción real — esa es `loverlipsyachts.com`). Se autorizó una conexión unificada en lugar del aislamiento local/remoto:

- Se revirtió la detección automática por `$_SERVER['HTTP_HOST']` en `api/conexion.php`, `setup_admin.php`, `test_db.php`, `test_email.php`.
- Se eliminó `core/.env.local`. Única fuente de verdad: `core/.env`.
- `core/.env` → `DB_HOST="localhost"` — válido porque Apache/PHP/MySQL viven en la misma máquina cPanel de tourfindy.com. **Consecuencia técnica, no opcional:** este valor solo resuelve correctamente cuando el código se ejecuta en ese servidor. Probar contra la base de datos desde XAMPP local ya no es posible — todo testing con BD ocurre desplegando a `lly.tourfindy.com`.
- La base local `lly_local_db` (MariaDB de XAMPP) se dejó intacta en el motor local pero queda sin uso por este proyecto — no se borró automáticamente (es dato, no código residual).

## Backend — snake_case (Mandamiento 7)

### Base de datos
- Tabla: `lly_users`
- Columnas: `id`, `email`, `password_hash`, `remember_token`, `token_expiry`, `created_at`

### Variables de entorno (`core/.env`)
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `DIAG_TOKEN` — gate de scripts de diagnóstico temporales

### Endpoints PHP
- `index.php` — gatekeeper server-side (sesión + remember-me)
- `dashboard.php` — markup privado, requiere `LLY_DASHBOARD_GATEKEEPER` definido
- `api/conexion.php` → clase `Conexion::getConnection(): PDO`
- `api/login.php` — POST únicamente
- `setup_admin.php` — temporal, token-gated, autobloqueo vía `core/.setup_admin.lock`

### Sesión / Cookies
- `$_SESSION['lly_user_id']`, `$_SESSION['lly_email']`
- Cookie `lly_remember` (HttpOnly, Secure, SameSite=Strict)

### Contrato de respuesta JSON — `api/login.php`
```json
{ "status": "success | error", "message": "string bilingüe", "data": [] }
```
HTTP 200 éxito · 401 credenciales inválidas · 400 input malformado · 405 método no permitido.

## Frontend
- `assets/js/auth.js` — intercepta `#login-form`, envía `FormData` (no JSON — `api/login.php` lee `$_POST`)
- IDs del DOM: `login-form`, `login-email`, `login-password`, `login-remember`, `login-error`, `login-submit`

## ✅ Cierre — Hito 5 (Parte C): `dashboard.php` completo
`dashboard.php` ya contiene el documento HTML íntegro (Guard PHP → `<head>` → Topbar de 3 pestañas → Hero → Hub Navigation → `#hub-reports` → `#hub-timeline` → `#hub-alliance` → Footer → `</main></body></html>` → `assets/js/main.js` deferred). Verificado: `php -l` sin errores, 6/6 `<section>` balanceadas, cero residuos de `hub-book`/`book-feature`.

### Hub de Timeline (`#hub-timeline`)
- UX cronológica inversa respetada: Phase 1 y Phase 2 (`active-phase`, `status-active`) renderizan primero en el feed; Phases 3–6 (`status-pending`) después.

### Hub de Alianza (`#hub-alliance`)
- Estatus conciliatorios canónicos aplicados (Mandamiento 10 — un solo nombre por concepto, reutilizando el vocabulario ya fijado en `#hub-reports`):
  - Phase 0 (cubierta) → badge `proposal-status-badge--done`: **Account Settled / Saldo Conciliado**
  - Phase 1 (en curso) → badge `proposal-status-badge--active`: **Awaiting Reconciliation / Por Conciliar**
  - Phase 2 (no iniciada) → sin cambio: `Upcoming / Próximo` (no es ninguno de los dos estatus canónicos)
- Modelo híbrido 50% Cash / 50% Trade Credits visible en la tabla financiera de cada fase y en el resumen `proposal-alliance-totals`.
