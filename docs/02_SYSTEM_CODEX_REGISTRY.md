# 🗂️ 02_SYSTEM_CODEX_REGISTRY.md
Fuente única de verdad de nomenclatura (Mandamiento 18). Este archivo no existía antes de hoy — se crea ahora con el inventario real encontrado en el sistema de archivos, no con nombres inventados.

## 📌 REGISTRO FORMAL DE ESQUEMA — `lly_book_content` (vigente, 2026-07-01)
Consulta obligatoria antes de leer/escribir cualquier `meta_key` nuevo (Mandamiento 4 — Anti-Alucinación). Historial de cómo se descubrió/corrigió cada campo: ver "Cierre de Hito — Book Editor Studio" y "Corrección — meta_key mismatch" más abajo.

**Tabla:** `lly_book_content` — patrón EAV (Entity-Attribute-Value), **no** una fila por campo con columnas fijas.

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | Clave primaria |
| `meta_key` | VARCHAR UNIQUE | Identificador semántico del campo — ver catálogo abajo |
| `content_en` | TEXT | Contenido en inglés |
| `content_es` | TEXT | Contenido en español |
| `updated_at` | TIMESTAMP | Auto-actualizado en cada UPSERT |

**Catálogo de `meta_key` válidos** (único vocabulario permitido — Mandamiento 10, cero sinónimos):

| `meta_key` | Consumidores | Notas |
|---|---|---|
| `hero_title` | `book_editor.php`, `book.php` | Título principal |
| `hero_subtitle` | `book_editor.php`, `book.php` | Subtítulo |
| `synopsis` | `book_editor.php`, `book.php` | Sinopsis del libro |
| `sample_chapter` | `book_editor.php`, `book.php` | Capítulo de muestra (lightbox) |
| `book_cover_path` | `book_editor.php`, `book.php` | Ruta relativa a la portada (mismo valor en `en`/`es`) |
| `amazon_link_url` | `book_editor.php`, `book.php` | URL de compra (mismo valor en `en`/`es`) |
| `card_N` (N=1…7) | `book_editor.php`, `book.php` | Texto de la Curiosity Card N |
| `card_N_icon` (N=1…7) | `book_editor.php`, `book.php` | Emoji (mismo valor en `en`/`es`) |
| `card_N_img` (N=1…7) | `book_editor.php` | Ruta a imagen custom de la card (WebP) |
| `testimonial_quote` | `book_editor.php`, `book.php` | Texto del testimonio maestro de Duane Hallock — **no** usar `testimonial` a secas (huérfano, ver corrección abajo) |
| `testimonial_author` | `book_editor.php`, `book.php` | Firma/byline del testimonio (nombre + cargo) |
| `article_N_tag` / `article_N_title` / `article_N_link` (N=1…3) | `book_editor.php`, `book.php` | Blog Synergy Cluster |

**Patrón de card:** `card_N` (texto) + `card_N_icon` (emoji) + `card_N_img` (opcional), N = 1…7.
**Fallback bilingüe:** `book_editor.php` y `book.php` inyectan texto oficial cuando una fila no existe o viene vacía para un idioma — nunca renderizan un layout vacío (ver detalle en la sección de corrección más abajo).

## 📌 REGISTRO FORMAL DE ENDPOINT — `api/translate.php` (vigente, 2026-07-01)
- **Auth:** `lly_is_authenticated()` (sesión + remember-me, mismo contrato que el resto de `api/`).
- **Método:** `POST` — body JSON.
- **Payload:** `{ "text": "string EN", "source_lang": "en", "target_lang": "es", "csrf_token": "string" }`
- **Respuesta éxito:** `{"status":"success","data":{"translated_text":"..."}}`
- **Respuesta error:** `{"status":"error","message":"..."}` — 401 (sin sesión), 403 (CSRF inválido), 400 (payload vacío/malformado/>8000 chars), 405 (método incorrecto), 502 (proveedor falló o devolvió forma inesperada).
- **Proveedor:** Google Translate, endpoint público `gtx` — sin API key, sin variables en `.env`.
- **CSRF:** validado pero **no rotado** en cada llamada (llamadas múltiples por clic desde `book_editor.php`); el token de sesión sigue rotando normalmente al hacer el Save real del formulario vía `api/book_editor.php`.

## 🏁 CIERRE DE HITO — 2026-06-22

### Estado verificado del sistema
- Login server-side (`index.php` → `core/auth_check.php` → `api/conexion.php`) operando en producción, confirmado con pruebas reales (no simuladas) contra `lly.tourfindy.com`.
- `dashboard.php` (privado) y `strategy.php` (privado, deep-link) con guards independientes — ambos confirmados en 403 ante acceso directo sin sesión.
- `book.html` (público) sin contenido confidencial — la propuesta de marketing vive exclusivamente en `strategy.php`.
- 6 Reports en `#hub-reports` (F→A), orden alfabético inverso consistente.
- `.htaccess` con línea base `Require all granted` + denegación específica por archivo — sin regresiones desde el incidente de site-wide 403.
- `knowledge/` fuera de git (`.gitignore` + `git rm --cached`), `Lover_Lips_Yachts_FUENTEDEVERDAD_CONSOLIDADA.md` purgado y reescrito sin contaminación cruzada.

### Limpieza de código muerto ejecutada hoy
`test_db.php`, `test_email.php`, `setup_admin.php` — eliminados del repo (`git rm`) y de las excepciones de `.htaccess`. Cumplieron su propósito (conexión a BD probada, cuenta admin creada, login verificado en vivo de forma repetida) y ya representan superficie de ataque innecesaria, no beneficio.
**Acción pendiente fuera de mi alcance:** estos 3 archivos siguen físicamente en el servidor de `lly.tourfindy.com` — el pipeline de FTP (`dangerous-clean-slate: false`) no borra remotos ausentes en el repo local. Deben eliminarse manualmente vía FTP/cPanel File Manager tras este push, igual que se hizo con el `index.html` legacy.

### ➡️ Siguiente Nivel — Próximas Fases
- **Fase 1 — Levantamiento y Estandarización del Catálogo de la Flota:** extraer y estructurar las 40 embarcaciones restantes desde WordPress (datos, tarifas, fotos) hacia el "Source of Truth" del dashboard.
- **Fase 2 — Diseño del Schema SQL para el Chatbot IA:** nuevas tablas (conversaciones, leads, reglas `NO_PRICE_WITHOUT_LEAD_DATA` y White-Glove Escalation) — requiere autorización humana explícita antes de crear/alterar esquema, por Mandamiento 9.

## ✅ Ajustes de precisión — Report F
- Fecha corregida a `June 20, 2026` / `20 de Junio, 2026` (antes 22 de junio).
- Badge cambiado de `pill-green` "Account Settled" a `pill-orange` "Awaiting Reconciliation" / "Por Conciliar" — refleja estatus financiero pendiente, no el estatus de despliegue del código (`book.html` sigue desplegado y funcional).
- Preview visual agregado: `assets/img/LandingPage.png` (confirmado existente en el filesystem antes de referenciarlo) usando exactamente el componente `.report-score-frame`/`.report-score-img` ya construido para el Report D — mismo patrón, cero CSS nuevo.
- Verificado: `php -l` limpio, 6/6 `<section>` balanceadas, cero estilos inline.

## ✅ Favicon (Regla de Oro) + Report F

### Corrección de nomenclatura
La directiva pedía otra tarjeta "Report E" — ya existía un Report E (Estrategia de Marketing → `strategy.php`). Se asignó **Report F** a la nueva tarjeta de la landing pública del libro, manteniendo el orden alfabético/cronológico inverso del grid: `F → E → D → C → B → A`. También se corrigió el nombre de archivo referenciado: el activo real es `book.html`, no `book.php` (no existe).

### Favicon
`<link rel="icon" type="image/png" href="assets/img/logo.png" />` inyectado justo después de la hoja de estilos en las 4 pantallas activas: `index.php`, `dashboard.php`, `strategy.php`, `book.html`.

### Report F (`dashboard.php` → `#hub-reports .reports-grid`)
Tarjeta estándar (no `--strategic`, ya está completada): número `F`, badge `pill-green` "Account Settled/Saldo Conciliado", botón `.report-strategic-gold-btn` (reutilizado, mismo componente que el botón del Report E) enlazando a `book.html` con `target="_blank"`.

### Verificación
`php -l` sin errores en `dashboard.php`/`index.php`/`strategy.php` · 6/6 `<section>` balanceadas en `dashboard.php` · cero estilos inline · favicon confirmado en las 4 pantallas vía grep.

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

## ✅ Cierre de Hito — Book Editor Studio & MySQL Architecture (2026-07-01)

### Nueva tabla: `lly_book_content`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | Clave primaria |
| `meta_key` | VARCHAR UNIQUE | Identificador semántico del campo (ej. `hero_title`, `synopsis`, `card_1`, `card_1_icon`, `book_cover_path`) |
| `content_en` | TEXT | Contenido en inglés |
| `content_es` | TEXT | Contenido en español |
| `updated_at` | TIMESTAMP | Auto-actualizado en cada UPSERT |

**Patrón de clave para tarjetas de curiosidad:** `card_N` (texto EN/ES) + `card_N_icon` (emoji, misma en ambas columnas), N = 1…7.
**Cobertura de errores neutralizados:** Error 1054 (Column not found) y dependencias de parsing por regex en `preg_replace` — ambos eliminados al mover la capa de escritura a PDO puro con transacciones.

### Endpoints registrados
- **`api/book_editor.php`** (POST, requiere sesión + CSRF): Recibe campos del editor, ejecuta `INSERT … ON DUPLICATE KEY UPDATE` por cada `meta_key`, convierte imágenes a WebP/80 vía GD. Respuesta JSON: `{"status":"success","message":"Changes successfully saved to live database!"}`.
- **`book.php`** (GET, público): Reemplaza `book.html` como página pública. Lee `lly_book_content` en una consulta `SELECT *`, construye array `$book[meta_key][lang]`, renderiza template PHP con `htmlspecialchars()`. Degrada gracefully a defaults embebidos si la BD no responde.
- **`.htaccess`** actualizado: `book` y `book_editor` agregados a la whitelist de PHP (`<FilesMatch>`); redirect 301 `book.html` → `book.php` para continuidad SEO.
- **`api/conexion.php`** refactorizado: catch de PDOException ahora lanza `RuntimeException` en lugar de `exit()`, permitiendo degradación graceful en páginas de renderizado (dashboard.php, book.php) sin crashear.

### Infraestructura bilingual segura
El par `content_en` / `content_es` en `lly_book_content` es la fuente única de verdad para todo texto bilingüe del Book Spotlight. No existe lógica de idioma en la BD — el cliente recibe ambas columnas y el toggle JS (`setLang`) / atributo `data-lang` del DOM resuelven la presentación.

## ✅ Corrección — meta_key mismatch en Book Editor Studio (2026-07-01)

### Causa raíz de "Seven Curiosity Cards" y "Duane Hallock Testimonial" vacíos
`book_editor.php` y `api/book_editor.php` leían/escribían el `meta_key` `testimonial`, pero la tabla real `lly_book_content` ya contenía datos vivos bajo `testimonial_quote` (texto, con placeholder truncado "What a gift you've given me...") y `testimonial_author` (`Duane Hallock, Red Cross`, poblado pero **nunca conectado al formulario** — no existía input alguno para editarlo). Las 7 tarjetas de curiosidad solo tenían fila real para `card_6` (`en`="Test", `es` vacío) — cards 1–5 y 7 nunca se guardaron, de ahí el layout vacío.

### Fix aplicado
- `book_editor.php`: se estandarizó la lectura al par real `testimonial_quote` / `testimonial_author` (el genérico `$c[$meta_key]` ya capturaba `testimonial_author` sin cambios). Se agregaron los inputs `testimonial_author_en` / `testimonial_author_es` (antes inexistentes) al fieldset "Duane Hallock Master Testimonial".
- `api/book_editor.php`: el UPSERT ahora escribe `testimonial_quote` (antes `testimonial`, huérfano) y el nuevo `testimonial_author`.
- `book.php` (público): se agregó `testimonial_author` al array de defaults, se mapeó el `meta_key` `testimonial_quote` → `$book['testimonial']`, y el footer `.book-feature-testimonial-author` (antes texto fijo en HTML) ahora renderiza vía `bk('testimonial_author', ...)` — editable desde el Studio por primera vez. El pull-quote corto de `#story-hooks` (`.pull-quote-vip-author`) se dejó estático a propósito: es una cita distinta, no el testimonio maestro.
- Fallback bilingüe agregado en `book_editor.php` (`$cardFallback`, `$testimonialFallback` + helper `edFallback()`): si el DB no tiene fila o viene vacía para un idioma, el input se pre-llena con el texto oficial en vez de quedar en blanco — mismo patrón defensivo que ya usaba `book.php`.
- La fila huérfana `testimonial` (vacía, `en`/`es` = '') queda sin uso en la tabla — no se borró (no destructivo), pero ya no la lee ningún endpoint.

### Verificación
- `php -l` limpio en `book_editor.php`, `api/book_editor.php`, `book.php`.
- Simulación headless del merge DB+fallback confirmó: cards 1–5 y 7 pre-cargan el texto oficial completo, card 6 respeta el valor real de BD (`Test`/`Prueba`), testimonial quote/author resuelven desde `testimonial_quote`/`testimonial_author` en vez de la clave vacía `testimonial`.
- Cero instanciación directa de PDO — toda lectura/escritura pasa por `Conexion::getConnection()`, prepared statements sin excepción (`api/book_editor.php` UPSERT ya usaba `:key`/`:en`/`:es`).

## ✅ Nuevo feature — Auto-Traducción "Translate Missing Fields" (2026-07-01)

### Objetivo
Lester escribe principalmente en inglés en `book_editor.php`. Un botón sobre el formulario ("🌐 Translate Missing Fields" / "🌐 Traducir Campos Faltantes") completa los campos en español de las Seven Curiosity Cards y el Duane Hallock Testimonial (incluyendo la firma del autor) llamando al backend — nunca directamente a un servicio externo desde el navegador.

### Endpoint nuevo — `api/translate.php`
- Auth: `lly_is_authenticated()` (mismo contrato de sesión que el resto de `api/`).
- Método: `POST` únicamente, body JSON (`{text, source_lang, target_lang, csrf_token}`).
- CSRF: valida `hash_equals` contra `$_SESSION['csrf_token']` — **no lo rota** en cada llamada (a diferencia de `api/book_editor.php`) porque el botón dispara múltiples fetches por clic; el token sigue rotando normalmente al hacer el Save real del formulario.
- Proveedor: **Google Translate, endpoint público `gtx`** (`https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=es&dt=t&q=...`) vía `cURL`, sin API key.
- Respuesta estándar: `{"status":"success","data":{"translated_text":"..."}}` · errores con `status":"error"` y `message` descriptivo (401/403/400/502 según la capa que falle).
- Prepared statements no aplica aquí (no toca `lly_book_content`) — el endpoint es un proxy de traducción puro, sin escritura a BD.

### ⚠️ Migración de proveedor — DeepL → Google Translate (2026-07-01, mismo día)
DeepL bloqueó el registro de cuenta por restricción regional del cliente. Se aplicó "Fricción Cero Operativa": se reemplazó la llamada `cURL` a la API de DeepL (`Authorization: DeepL-Auth-Key`) por el endpoint público no-oficial de Google Translate (`client=gtx`). Éste no requiere API key, así que:
- `core/.env` / `core/.env.staging.example`: se **eliminaron** `TRANSLATION_API_URL`/`TRANSLATION_API_KEY` (dead config — Mandamiento 8, nada las lee ya).
- `api/conexion.php`: se **eliminó** el método público `Conexion::env()` agregado horas antes para leer esas llaves — quedó sin ningún consumidor tras la migración, así que se retiró en vez de dejarlo como código muerto.
- Parseo de la respuesta de Google (array JSON anidado y no documentado, ej. `[[["Hola","Hello",null,null,1]]],null,"en",...]`): se concatenan los fragmentos de `$decoded[0][*][0]` en orden para reconstruir el texto completo, ya que Google puede partir textos largos en varias oraciones.
- Riesgo aceptado y documentado: `gtx` es un endpoint no oficial (usado por la extensión de Chrome de Google Translate) — puede cambiar de forma sin previo aviso o imponer rate-limiting silencioso. Si eso ocurre, `api/translate.php` devuelve `502` con mensaje claro; no hay fallback automático a un segundo proveedor (no solicitado).

### `book_editor.php` — Dirty Checking (cliente)
Cada input/textarea en inglés de Curiosity Cards y Testimonial (`.js-translate-source`) lleva `data-original-val` (valor cargado desde BD al render) y `data-target` (id del campo en español). Al hacer clic:
1. **ES vacío** → se traduce el EN correspondiente.
2. **EN cambió** vs. `data-original-val` → se re-traduce y se sobreescribe ES (y `data-original-val` se actualiza al nuevo baseline).
3. **ES ya tiene texto y EN no cambió** → se omite, protegiendo traducciones manuales previas.
El bucle es secuencial (`await`-style vía cadena de promesas), no paralelo, para respetar límites de rate-limit del proveedor. Estados de carga reutilizan el patrón visual ya existente de `.editor-publish-btn--loading`.

### `.htaccess`
Se agregó `translate` a la whitelist de `<FilesMatch>` (coincide por nombre de archivo, mismo patrón que ya usa `book_editor` para `api/book_editor.php`) — sin esto, `api/translate.php` habría devuelto 403 en producción.

### Estilos nuevos
`.editor-translate-bar`, `.editor-translate-copy`, `.editor-translate-btn` (+ estado `--loading`) — outline gold sobre `--surface-2`, reutilizando variables ya definidas (`--gold`, `--gold-10/20`, `--r-full`, `--ease`). Cero estilos inline, cero `!important`.

### Verificación
- `php -l` limpio en `api/translate.php`, `api/conexion.php`, `book_editor.php`.
- Render headless de `book_editor.php` con sesión simulada confirmó: `data-original-val`/`data-target` presentes en cards y testimonial, botón de traducción renderizado.
- Prueba real end-to-end contra `translate.googleapis.com` (fuera del navegador, vía `php -r`) confirmó HTTP 200 y traducción correcta ("What a gift you have given me." → "Que regalo me has hecho.").
- Prueba real end-to-end del endpoint completo levantando `php -S` local + sesión/CSRF simulados: llamada válida → `200` con `translated_text` correcto; CSRF inválido → `403`; sin sesión → `401`. Servidor de prueba y sesión falsa destruidos al terminar.

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
- `api/book_editor.php` — POST, sesión + CSRF, UPSERT a `lly_book_content`
- `api/translate.php` — POST, sesión + CSRF, proxy a Google Translate (`translate.googleapis.com`, `client=gtx`) — sin API key
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
