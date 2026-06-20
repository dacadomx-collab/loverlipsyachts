# 🗂️ 02_SYSTEM_CODEX_REGISTRY.md
Fuente única de verdad de nomenclatura (Mandamiento 18). Este archivo no existía antes de hoy — se crea ahora con el inventario real encontrado en el sistema de archivos, no con nombres inventados.

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
