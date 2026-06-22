# 🗂️ 02_SYSTEM_CODEX_REGISTRY.md
Fuente única de verdad de nomenclatura (Mandamiento 18). Este archivo no existía antes de hoy — se crea ahora con el inventario real encontrado en el sistema de archivos, no con nombres inventados.

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
