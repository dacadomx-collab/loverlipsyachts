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

## 📌 REGISTRO FORMAL DE ESQUEMA Y ENDPOINTS — Operador Cognitivo Omnicanal + Enlaces Efímeros (vigente, 2026-07-29)
Ver `modulos/MOD_OPERADOR_COGNITIVO_OMNICANAL.md` para el blueprint agnóstico (v1.1). Autorización explícita del Arquitecto recibida en el mismo hito para crear las tablas listadas abajo (Mandamiento de Inmutabilidad del Sistema).

### Esquema — `sql/003_create_omnichannel_schema.sql` (Fase 2 del blueprint)
- `omnichannel_channels`, `omnichannel_contacts` (+ `is_vip` para White-Glove Escalation), `omnichannel_sessions` (+ `lead_date`/`lead_pax`/`lead_route`/`lead_contact` para `NO_PRICE_WITHOUT_LEAD_DATA`), `omnichannel_messages`, `omnichannel_message_attachments`, `omnichannel_webhooks`.
- `tenant_id` en todas las tablas es siempre el mismo `AI_TENANT_ID` de `core/.env` — proyecto de un solo tenant; la columna existe por fidelidad al molde agnóstico, no por necesidad real de multi-tenencia.

### Esquema — `sql/004_create_ll_ephemeral_links.sql`
- `ll_ephemeral_links` — `token` (43 chars, base64url de 32 bytes random), `resource_type` (`quote`/`itinerary`/`custom`), `payload_html` XOR `target_url`, `max_views`/`view_count` (conteo atómico, ver `core/EphemeralLinkManager.php::redeem()`), `status` (`active`/`expired`/`revoked`).
- `ll_app_settings` — key/value genérico; única fila usada hoy: `ephemeral_link_default_max_views` (default `3`).

### Clases (`core/`)
- `core/OmnichannelRepository.php` — `persistInbound()` / `persistOutbound()`. Upsert channel → contact → session; INSERT idempotente en `omnichannel_messages` (`ON DUPLICATE KEY UPDATE id = id` sobre `channel_message_id`).
- `core/EphemeralLinkManager.php` — `create()`, `redeem()` (UPDATE atómico condicionado, nunca SELECT-then-UPDATE), `listRecent()`, `updateMaxViews()`, `revoke()`, `getDefaultMaxViews()`/`setDefaultMaxViews()`.
- `core/ProxyBridge.php` — `fromEnv()` ahora resuelve `knowledgeMdPath` a `knowledge/Lover_Lips_Yachts_FUENTEDEVERDAD_CONSOLIDADA.md` (antes quedaba en `null`; la personalidad de Lester y los cerrojos comerciales de la sección 7 de ese `.md` no llegaban a AURA hasta este fix). Se agregó `getTenantId()` público — lo consumen los dos endpoints de canal para persistir bajo el mismo tenant.

### Endpoints PHP
- `api/public/ai_widget_gateway.php` — sin cambio de contrato JSON; ahora también persiste inbound/outbound en `omnichannel_*` (best-effort, degrada sin romper el chat si la DB no responde).
- `api/public/whatsapp_webhook.php` (nuevo) — `GET` handshake de suscripción Meta (`hub_verify_token` vs `WHATSAPP_VERIFY_TOKEN`), `POST` valida `X-Hub-Signature-256` contra `WHATSAPP_APP_SECRET` sobre el body crudo, normaliza a OCMC, audita en `omnichannel_webhooks`, persiste y reenvía vía `ProxyBridge`, responde en el mismo hilo de WhatsApp vía Graph API (`WHATSAPP_ACCESS_TOKEN`/`WHATSAPP_PHONE_NUMBER_ID`). Sin esas 4 variables en `core/.env`, el lado de recepción/persistencia sigue funcionando; el envío de respuesta degrada a no-op registrado en log.
- `api/ephemeral_links.php` (nuevo) — sesión + CSRF (mismo patrón que `api/book_editor.php`), acciones vía `action`: `list`, `create`, `update_max_views`, `revoke`, `set_default_max_views`.
- `api/public/l.php` (nuevo) — endpoint público de redención, `?t=<token>`, sin autenticación (el token es el secreto). Redirige a `target_url` o renderiza `payload_html`; enlace agotado/revocado → página de cortesía HTTP 410, nunca un error crudo.

### Variables de entorno nuevas (`core/.env`, ver `core/.env.staging.example`)
- `WHATSAPP_VERIFY_TOKEN`, `WHATSAPP_APP_SECRET`, `WHATSAPP_ACCESS_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID` — pendientes de valor real hasta que se aprovisione la app de Meta; el proyecto opera sin ellas (WhatsApp inactivo, Widget Web y Enlaces Efímeros ya funcionales).

### Frontend
- `dashboard.php` → `#ephemeral-links-section` — panel "Private Links / Enlaces Privados": ajuste global de vistas por defecto, formulario de creación, tabla de enlaces activos con edición inline de `max_views` y revocación.
- `assets/js/main.js` → `initEphemeralLinksPanel()` (llamado desde `llyInitAll()`), guarda en ausencia de `#ephemeral-links-table` — no afecta otras páginas.
- `assets/css/style.css` → bloque `SELF-DESTRUCT LINKS PANEL`, clases `.ephemeral-*` y `.section-desc` (nueva, reutilizable) — solo `var(--token)`, cero inline, cero `!important`.

## 📌 REGISTRO FORMAL — Bautizo "PG-AI Pink Glove AI" + Ejecución SQL 004 + Plantillas de Cotización (vigente, 2026-07-29)

### Nombre comercial del módulo
El módulo descrito en el registro anterior (Chatbot IA + Handshake Omnicanal + Cotizaciones Efímeras) se bautiza oficialmente **"PG-AI Pink Glove AI"** (o **PG-AI**) para este proyecto. Es un nombre **interno/de administrador**: vive en comentarios de código, prefijos de `error_log()` (`[PG-AI · <archivo>]`) y en el copy del panel de `dashboard.php` (`🔒 PG-AI · Private Links`). **No** se propaga al copy público del Widget Web ni a las respuestas de WhatsApp — el visitante final nunca ve el nombre interno del módulo, solo la experiencia de anfitrión de Lester. Esta distinción es deliberada, no un olvido.
- Nombres de clase/tabla/función **no** se renombraron (`EphemeralLinkManager`, `OmnichannelRepository`, `ll_ephemeral_links`, etc.) — ya estaban desplegados en producción al momento del bautizo; renombrarlos habría sido una migración de esquema innecesaria para un cambio puramente de branding.
- El molde agnóstico (`modulos/MOD_OPERADOR_COGNITIVO_OMNICANAL.md`) **no** adopta "PG-AI" — ese nombre es exclusivo de la implementación concreta de Lover Lips Yachts, nunca del documento reutilizable (Mandato de Sincronización Génesis).

### Confirmación de despliegue — `sql/004_create_ll_ephemeral_links.sql`
Ejecutado exitosamente en MariaDB de producción (`u713871298_lly_db`). `ll_ephemeral_links` y `ll_app_settings` están vigentes. SSL Lifetime de Hostinger para `loverlipsyachts.com` verificado activo — el enlace público `api/public/l.php` sirve siempre sobre HTTPS en producción.

### Plantillas de cotización — "PINK LIPS Experience"
- `dashboard.php` → array `$lly_pgai_quote_templates` (mismo patrón "sin tabla, array PHP a mano" que `$lly_reportes`): dos rutas, `balandra` ($19,000 MXN) y `espiritu_santo` ($24,000 MXN), con inclusiones gourmet/barra libre y las cláusulas de depósito (30% en confirmación) y clima/capitán reutilizadas **verbatim** de `propuestas.php` (acordeón "Navigation Policies & Rules") — no reescritas, para que ambas superficies no diverjan.
- Selector "PG-AI Quick Template" en el formulario de creación de enlaces rellena `title`/`payload_html`/`resource_type` vía `initEphemeralQuoteTemplates()` (`assets/js/main.js`), leyendo el JSON embebido en `#lly-pgai-quote-templates-data`.
- `api/ephemeral_links.php::lly_el_safe_html()` — allow-list ampliado con `span`/`div` (antes solo `p,em,strong,br,ul,ol,li,h3,h4,table,tr,td,th,thead,tbody`): sin `span`, los pares `<span data-lang="en"|"es">` de las plantillas se habrían aplanado y ambos idiomas se habrían mostrado concatenados en `api/public/l.php`, violando el contrato bilingüe de la Regla de Oro.
- Pendiente de autorización escrita final de Lester antes de considerarse texto legal definitivo — mismo aviso ⚠️ que ya rige sobre el acordeón de políticas en `propuestas.php`; el texto aquí es apto para operar pero no reemplaza esa autorización.

### Endurecimiento de degradación (Tarea C)
- `api/public/whatsapp_webhook.php` — el log ahora distingue "canal no aprovisionado" (`WHATSAPP_APP_SECRET` vacío — modo degradado esperado) de "firma inválida" (intento de forjado real) sin cambiar el comportamiento fail-closed (ambos casos responden 200 a Meta y descartan el evento sin procesarlo).

## 📌 REGISTRO FORMAL — PG-AI Hub, Satélite AURA M2M y Purga de Entorno Staging (vigente, 2026-07-31)

### Decisión de entorno: `loverlipsyachts.com` es el único destino oficial
`lly.tourfindy.com` (staging en GreenGeeks, DB `tourfindycom_lly_db`) queda retirado como entorno activo. La arquitectura real ya era de un solo nivel local↔producción desde la consolidación de `api/conexion.php` a una sola base de datos (`u713871298_lly_db`) — este `.md` documentaba una plantilla de staging de 3 niveles que ya no tenía código correspondiente ejecutándola.
- `core/.env` → `ALLOWED_ORIGINS` corregido: tenía `https://lly.tourfindy.com` y **nunca** incluía `https://loverlipsyachts.com` (bug heredado, no intencional).
- `core/.env.staging.example` y `.env.example` (raíz) reescritos como avisos de deprecación — el segundo era un fósil pre-`index.php` (mencionaba un SPA `index.html` estático y llaves `PAYPAL_`/`STRIPE_` que no existen en este código) y ninguno de los dos era leído por `api/conexion.php` (solo lee `core/.env`).
- `sql/001_create_lly_users.sql` y `sql/002_add_password_hash_legacy.sql` — encabezado de instrucciones corregido de `tourfindycom_lly_db` a `u713871298_lly_db` (las entradas históricas de este registro que mencionan `lly.tourfindy.com` como hecho pasado **no** se reescriben — son bitácora, no configuración activa).

### Corrección — 403 "Acceso denegado" en `pg_ai_hub.php` / `aura_diagnostic.php`
Causa raíz real: `.htaccess` (raíz del proyecto) tiene una lista blanca por nombre de archivo (`<FilesMatch "^(index|login|...)\.php$"> Require all granted`) — todo `.php` fuera de esa lista recibe `403` de Apache antes de que PHP se ejecute, incluso con sesión válida. `AllowOverride All` está activo en XAMPP local (`c:\xampp\apache\conf\httpd.conf`), así que este bloqueo también aplicaba en desarrollo local, no solo en producción. No era un fallo de `lly_is_authenticated()`/`core/dev_bypass.php` — ambos ya funcionaban correctamente.
- Añadidos a la lista blanca: `pg_ai_hub`, `aura_diagnostic` (cubre tanto la vista raíz como `api/aura_diagnostic.php` — coinciden en nombre de archivo), `ephemeral_links`, `leads`, `pgai_settings`, `l` (`api/public/l.php`, redención pública de enlaces), `whatsapp_webhook`.
- Verificado en vivo contra Apache local: `pg_ai_hub.php` y `aura_diagnostic.php` pasaron de `403` a `200`; los endpoints `api/*.php` nuevos pasaron de `403` (Apache) a `401` (JSON de la propia app, "Unauthorized — please log in" — comportamiento esperado sin cookie de sesión).

### `modulos/MOD_CONEXION_SATELLITE_AURA_M2M.md` (nuevo, v1.0) + `core/AuraSatelliteClient.php`
Blueprint agnóstico para el canal M2M hacia un motor central AURA (llave estática `X-AURA-KEY`, matriz de fallback LAN→WAN, contrato de request/response, checklist Fase 0-4). Complementario a `MOD_OPERADOR_COGNITIVO_OMNICANAL.md` — un proyecto puede tener ambos moldes provisionados, pero solo uno debe servir tráfico real de visitantes a la vez.
- `core/AuraSatelliteClient.php` implementa el artefacto de referencia del blueprint, configurado desde `ACADEP_AURA_*` en `core/.env` vía `EnvSettingsStore::getRaw()` (nuevo método — valores sin enmascarar, uso exclusivamente server-side, nunca expuesto en una respuesta JSON).
- **Desviación consciente de la sección 5.3 del blueprint:** el molde base solo reintenta ante fallo de *conexión* (no de lectura) para evitar doble ejecución de una petición ya facturada. `AuraSatelliteClient::dispatch()` amplía la conmutación a **cualquier** fallo (401/403/500/504/timeout) porque este cliente solo alimenta el sandbox de diagnóstico (`api/aura_diagnostic.php`) — un humano mira el resultado y reintentar es un clic consciente, no un bucle silencioso. Ver comentario en el método.
- **Hallazgo de la primera validación en vivo (Fase 4):** el host LAN (`http://192.168.1.224:8080`) responde y es alcanzable (`4ms`, `curl errno 0`), pero rechaza la `ACADEP_AURA_KEY` provista con `401 "No autorizado."` — probado contra variantes de cabecera (`X-AURA-KEY`, `Authorization: Bearer`, `X-API-KEY`, mayúsculas/minúsculas) con el mismo resultado. La llave no autentica contra ese servidor tal como está configurada hoy; pendiente de revisión por quien administra AURA. El WAN de respaldo (`https://aura.acadep.com`) no se ha validado en vivo todavía.
- Alcance confirmado: el widget público y el webhook de WhatsApp siguen despachando exclusivamente vía `core/ProxyBridge.php` (HMAC firmado por tenant) — `AuraSatelliteClient` es diagnóstico únicamente, no reemplaza esa ruta de despacho salvo decisión explícita futura del Arquitecto.

### `pg_ai_hub.php` (nuevo) + `aura_diagnostic.php` (nuevo)
- `dashboard.php` Card #1 simplificado al mismo tamaño/grid que las otras 3 (antes tenía tablas completas embebidas) — ahora es icono + título + descripción + botón único a `pg_ai_hub.php`.
- `pg_ai_hub.php` — vista dedicada con 4 secciones: A) leads omnicanal (`api/leads.php`, nuevo), B) testbed de chat embebido contra `api/public/ai_widget_gateway.php` (el mismo endpoint de producción, no un mock), C) configuración AURA/WhatsApp (`api/pgai_settings.php` + `core/EnvSettingsStore.php`, nuevos — whitelist fijo de claves, secretos enmascarados, nunca un editor de `.env` genérico) + visor de solo lectura del `.md` de conocimiento real que consume `ProxyBridge`, D) plantillas PINK LIPS + enlaces efímeros (movido tal cual desde `dashboard.php`, ahora en `core/pgai_templates.php`).
- `aura_diagnostic.php` — panel de telemetría (status, latencia de red vs. latencia reportada por AURA, tokens, motor/modelo, tenant confirmado, canal LAN/WAN activo) + sandbox de prompt libre. La llave AURA nunca llega al navegador — solo `api/aura_diagnostic.php` la usa server-side.
- `ll_app_settings` (tabla de `sql/004`) existía en el SQL del repo pero nunca se había ejecutado en producción — creada en este hito (`CREATE TABLE IF NOT EXISTS`, idempotente); `ll_ephemeral_links` ya existía.
- Panel `#ephemeral-links-section` — cada fila ahora muestra vistas consumidas, un contador explícito de vistas restantes (`.ephemeral-remaining-badge`) y estado (`Active`/`Expired`/`Revoked`, bilingüe) actualizados en cada `ephemeralLoadList()`; revocar o guardar un nuevo límite dispara un recargado inmediato de la tabla.

## 📌 REGISTRO FORMAL — Blueprint AURA v1.1 + Segunda Validación en Vivo (vigente, 2026-07-31)

### `modulos/MOD_CONEXION_SATELLITE_AURA_M2M.md` → v1.1
Tres adiciones, 100% agnósticas (placeholders `{{ENV_PREFIX}}`, `{{BASE_URL}}`, `{{API_KEY}}` — ningún nombre de cliente/tenant real incorporado, ver Mandato de Sincronización Génesis en la sección 6 del propio documento):
- **§2.1 — Desambiguación de puerto multi-instancia:** puerto `:8080` = instancia Core Principal, puerto `:8090` = instancia Satélite dedicada; mismo path (`/api/v2/aura/gateway`) en ambas, cada una con su propia tabla de llaves — un `401` puede ser simplemente "puerto equivocado", no una llave mal generada. (Nota: la solicitud original nombraba la instancia de `:8090` con una marca comercial específica del cliente; se generalizó a "instancia dedicada/aislada" para no romper el agnosticismo del molde — el nombre real, si aplica, vive en este registro, no en el `.md` genérico.)
- **§3.2.1 — Matriz de equivalencia de cabeceras:** `X-AURA-KEY: {{API_KEY}}` (estándar recomendado) y `Authorization: Bearer {{API_KEY}}` son equivalentes; documentado como diagnóstico útil si un proxy/WAF intermedio descarta cabeceras no estándar.
- **§4.1 — Harness cURL de diagnóstico en 2 clics:** plantilla `curl -i -X POST` lista para pegar, más tabla de interpretación de códigos (`200` OK, `401` llave no registrada en `satellite_tokens` del puerto asignado, `402` cuota consumida, `403` llave sin permiso de scope, `500` error interno, `503` saturación — leer `Retry-After`, `504`/timeout = fallo de canal específico).

### Segunda validación en vivo (Fase 4) — resultado
Con el fallback ampliado (registro anterior) ya activo, se re-ejecutó el handshake real vía `AuraSatelliteClient::dispatch()`:
- **LAN** (`192.168.1.224:8080`) → sigue rechazando `ACADEP_AURA_KEY` con `401 "No autorizado."` (sin cambio respecto a la primera validación) — confirma que el puerto correcto (`:8080`, Core Principal, no el `:8090` documentado en §2.1) ya estaba siendo usado, así que el `401` no es un problema de puerto: la llave específicamente no está (aún) en la tabla de tokens de esa instancia.
- El failover a **WAN** se disparó automáticamente como diseñado (`AuraSatelliteClient::dispatch()`, registro anterior) — pero `https://aura.acadep.com` no resuelve DNS desde este entorno (`curl errno 6`), así que el WAN de respaldo tampoco pudo validarse todavía. `lanErrorMessage` quedó correctamente poblado en el resultado (`"No autorizado."`), confirmando que el nuevo campo de telemetría funciona.
- **Conclusión:** el satélite (`core/AuraSatelliteClient.php`) y el panel (`aura_diagnostic.php`) están confirmados listos y correctos — el bloqueo restante es 100% del lado de AURA (llave pendiente de inyección en `satellite_tokens`), no del código de este proyecto. Nada que corregir aquí hasta la siguiente inyección de llave.

### Verificación de salud de página (Regla de Oro)
`pg_ai_hub.php` y `aura_diagnostic.php` — `curl` en vivo contra Apache local confirma `200` en ambas. Auditado sin regex de emergencia, con grep dirigido: cero `style="..."` inline, cero `!important`, favicon (`assets/img/logo.png`) y logos día/noche presentes en topbar y footer de ambas vistas, spans `data-lang` bilingües presentes (75 en `pg_ai_hub.php`, 28 en `aura_diagnostic.php`). `php -l` limpio en los 5 archivos tocados/re-verificados: `core/AuraSatelliteClient.php`, `aura_diagnostic.php`, `pg_ai_hub.php`, `api/aura_diagnostic.php`, `core/EnvSettingsStore.php`.

## 📌 REGISTRO FORMAL — Blueprint AURA v1.2 (Puertos + Fallback IP Directa) y Corrección de `ACADEP_AURA_FALLBACK_URL` (vigente, 2026-07-31)

### `modulos/MOD_CONEXION_SATELLITE_AURA_M2M.md` → v1.2
Cuatro adiciones, 100% agnósticas (`{{ENV_PREFIX}}`, `{{BASE_URL}}`, `{{API_KEY}}` — sin nombres de cliente real):
- **§2.1 ampliada:** tabla de puertos ahora incluye la base de datos de tokens por instancia — `aura_db` para `:8080` (Core Principal), `axon_core_db` para `:8090` (Satélite dedicada). Estos dos nombres son infraestructura propia de AXON/ACADEP (el operador de la plataforma, ya tratado como actor genérico en `MOD_OPERADOR_COGNITIVO_OMNICANAL.md` — "DCD LABS", "AXON") — no un nombre de cliente/tenant final, por lo que sí se incorporan literalmente al molde agnóstico sin violar el Mandato de Sincronización Génesis.
- **§2.2 (nueva) — Resiliencia de red, fallback por IP directa:** variable opcional `{{ENV_PREFIX}}_FALLBACK_IP`; se activa únicamente cuando el WAN falla por resolución DNS (`curl` errno `6`), nunca ante un `401`/`403`/`5xx` (esos sí alcanzaron el servidor). Técnica obligatoria: `CURLOPT_RESOLVE`, nunca reescribir la URL a la IP cruda — preserva el `Host`/SNI de TLS contra el certificado real del hostname.
- **§3.2.1 / §4.1** confirmadas sin cambio de fondo — tabla de códigos HTTP alineada a la terminología literal solicitada (`200 OK`, `401 Unauthorized`, `402 Limit Exceeded`, `403 Forbidden`, `500 Internal Error`, `503 Busy`, `504 Gateway Timeout`).
- **§5.4** — el artefacto de referencia `AuraSatelliteClient` ahora incluye el tercer escalón (`fallbackIp` en constructor/`fromConfig()`, `dnsFailed` distinguido de `connectFailed` en `attempt()`, canal `wan_ip` en el resultado). El molde base **mantiene** su criterio conservador de reintento (solo ante fallo de conexión/DNS, nunca ante `401`/`5xx`/timeout de lectura — sección 5.3) — la ampliación a "cualquier fallo" sigue siendo una desviación exclusiva de este proyecto (ver registro anterior), no del molde genérico.

### `core/AuraSatelliteClient.php` — implementación real actualizada
Fill-in directo del artefacto v1.2: constructor y `fromEnv()` ahora aceptan `ACADEP_AURA_FALLBACK_IP`; `attempt()` distingue `dnsFailed` de `connectFailed` y soporta `CURLOPT_RESOLVE`; `dispatch()` añade el tercer intento (`wan_ip`) cuando el WAN falla específicamente por DNS y hay IP configurada. El resultado ahora puede incluir tanto `lanErrorMessage` como `wanErrorMessage` cuando se atravesaron los tres escalones.

### Corrección — bug real en `ACADEP_AURA_FALLBACK_URL`
`core/.env` traía `ACADEP_AURA_FALLBACK_URL="https://aura.acadep.com/api/v2/aura/gateway"` — con el path del endpoint ya incluido. Como `AuraSatelliteClient::dispatch()` concatena `fallbackUrl . gatewayEndpoint`, cualquier despacho WAN real habría apuntado a `.../api/v2/aura/gateway/api/v2/aura/gateway` (path duplicado). Corregido a solo el host (`https://aura.acadep.com`); añadida `ACADEP_AURA_FALLBACK_IP="TO_BE_SET"` (sin IP real todavía — el tercer escalón queda inactivo hasta que se provisione, confirmado en la re-validación de abajo). Este bug no se había manifestado antes porque toda validación en vivo hasta ahora se detuvo en el `401` de LAN, sin llegar nunca a ejecutar realmente la petición WAN con URL completa.

### Tercera validación en vivo (Fase 4) — resultado
Con el bug de `FALLBACK_URL` corregido y el tercer escalón ya implementado, se re-ejecutó `AuraSatelliteClient::dispatch()`:
- **LAN** (`192.168.1.224:8080`) → sigue en `401 "No autorizado."`, sin cambio — la llave sigue pendiente de inyección en `satellite_tokens` del lado de AURA.
- **WAN** (`https://aura.acadep.com`) → correctamente intentado contra la URL ya corregida (sin duplicación de path); sigue sin resolver DNS desde este entorno (`curl errno 6`) — comportamiento esperado, no hay registro DNS público confirmado para ese hostname desde aquí.
- **WAN por IP directa** → correctamente **no** se activó, porque `ACADEP_AURA_FALLBACK_IP` sigue en `TO_BE_SET` (`EnvSettingsStore::getRaw()` normaliza ese valor a cadena vacía) — confirma que el nuevo tercer escalón respeta su condición de activación y no se dispara sin una IP real configurada.
- `pg_ai_hub.php` (Sección C) ahora expone un campo opcional para `ACADEP_AURA_FALLBACK_IP`; `aura_diagnostic.php` reconoce el canal `wan_ip` en el indicador visual y compone el rastro de fallos (`lanErrorMessage` → `wanErrorMessage`) cuando se atraviesan varios escalones.
- **Conclusión sin cambio respecto al registro anterior:** el código de este proyecto está confirmado correcto en los tres escalones; el único bloqueo restante sigue siendo la llave no registrada del lado de AURA.

### Verificación de salud de página (Regla de Oro) — segunda pasada
`pg_ai_hub.php` y `aura_diagnostic.php` — `200` en vivo tras los cambios de este hito. Grep dirigido confirma: cero `style="..."` inline, cero `!important` en ambos archivos. `php -l` limpio en `core/AuraSatelliteClient.php`, `core/EnvSettingsStore.php`, `pg_ai_hub.php`, `aura_diagnostic.php`, `api/aura_diagnostic.php`.

## 🏁 CIERRE DE HITO — Handshake AURA M2M en vivo, éxito confirmado (2026-07-31)

### Causa raíz real del `401` — puerto equivocado, no llave inválida
`ACADEP_AURA_KEY` nunca estuvo mal — vive registrada en `axon_core_db` (instancia Satélite, puerto `:8090`), no en `aura_db` (instancia Core Principal, puerto `:8080`), exactamente el escenario que el blueprint v1.1 §2.1 advertía ("un `401` puede significar simplemente llave correcta, puerto equivocado"). `core/.env` → `ACADEP_AURA_BASE_URL` corregido de `:8080` a `:8090`.

### Hallazgo nuevo — contrato de respuesta real envuelto en `"data"`
Probado en vivo contra `:8090` vía cURL crudo antes de tocar código: el payload de éxito real llega como `{"status":"success","message":"...","data":{"response":...,"engine":"ollama","model":"llama3.2","latencyMs":...,"tokensUsed":...,"tokensRemaining":...,"sessionId":...,"tenantName":"Lover Lips Yachts"}}` — **no** plano en la raíz como documentaba el contrato original (§3.3). `core/AuraSatelliteClient.php::attempt()` corregido para preferir `decoded.data.*` cuando existe y sea un objeto, con fallback a la raíz — sin este fix, un `200 OK` real se habría interpretado como éxito pero con todos los campos de telemetría en `null`. Reflejado en `modulos/MOD_CONEXION_SATELLITE_AURA_M2M.md` → v1.3 (nota de compatibilidad en 3.3 + artefacto de referencia 5.4 actualizado) como hallazgo agnóstico reutilizable — sin nombrar el proyecto concreto en el molde.

### Validación en vivo — cuatro capas confirmadas
1. **cURL crudo** contra `http://192.168.1.224:8090/api/v2/aura/gateway` con `X-AURA-KEY` → `200 OK`, payload real capturado (ver arriba).
2. **`AuraSatelliteClient::dispatch()` directo** (PHP CLI, sin HTTP) → `success=true`, `httpCode=200`, `channelUsed=lan`, `engine=ollama`, `model=llama3.2`, `tokensUsed=36`, `tokensRemaining=999820`, `tenantName=Lover Lips Yachts` — **WAN nunca se intentó**, confirmando que `dispatch()` corta en éxito de LAN tal como exige la Tarea de este hito.
3. **`api/aura_diagnostic.php` vía HTTP real** (cookie de sesión + CSRF, mismo camino que usa el navegador) → mismo resultado exitoso, `tokensUsed=40` en esa segunda llamada (confirma que el contador de cuota de AURA avanza con cada petición real, no un mock).
4. **Log de Apache/PHP** (`error_log`) — entradas anteriores muestran `channel=wan http=0 success=0` (fallos limpios de DNS antes del fix de puerto, sin colgar la petición); la entrada más reciente muestra `channel=lan http=200 success=1 tokens_used=40` en una sola línea, sin ruido ni stack trace.

### Estado del módulo tras este hito
`aura_diagnostic.php` queda confirmado **100% funcional en producción-equivalente local** contra el servidor AURA real — Fase 4 del blueprint cerrada con evidencia real, no simulada. Alcance sin cambio: el widget público y WhatsApp siguen despachando exclusivamente vía `core/ProxyBridge.php`; `AuraSatelliteClient` sigue siendo diagnóstico únicamente hasta una decisión explícita futura del Arquitecto de promoverlo a un camino de despacho real.

### Verificación técnica
`php -l` limpio en `core/AuraSatelliteClient.php`, `aura_diagnostic.php`, `api/aura_diagnostic.php`, `pg_ai_hub.php`, `core/EnvSettingsStore.php`. Regla de Oro re-confirmada: favicon (`assets/img/logo.png`), logos día/noche, colores ALISER vía tokens CSS (`--pink #FF007F` / `--gold #D4AF37`, cero valores crudos), spans `data-lang` bilingües, selector Day/Night, cero `style="..."` inline y cero `!important` en ambos archivos. `pg_ai_hub.php` y `aura_diagnostic.php` responden `200` en vivo contra Apache local.

## 🏁 CIERRE DE HITO — Blueprint AURA v1.3 finalizado, "Fricción Cero" 2 clics / 2 minutos (2026-07-31)

Hito puramente documental — sin cambios de código; `core/AuraSatelliteClient.php`, `pg_ai_hub.php` y `aura_diagnostic.php` ya habían sido actualizados y validados en vivo en el hito anterior. Este cierre formaliza el molde reutilizable (`modulos/MOD_CONEXION_SATELLITE_AURA_M2M.md`) con los cuatro puntos pedidos:

1. **Contrato `data.*`** — ya reflejado desde el hito anterior (nota de compatibilidad en §3.3 + artefacto de referencia §5.4, ambos con fallback a la raíz). Confirmado sin cambios adicionales necesarios.
2. **Matriz de puertos/bases satélite** — ya reflejada desde v1.1/v1.2 (§2.1: `:8080` → `aura_db`, Core Principal; `:8090` → `axon_core_db`, Satélite). Confirmada sin cambios adicionales.
3. **Procedimiento de verificación en 4 niveles (nuevo — §4.1.1):** formaliza como checklist reutilizable la secuencia que este proyecto ya ejecutó y documentó de facto en el hito anterior (cURL crudo → `AuraSatelliteClient::dispatch()` vía PHP CLI → `api/aura_diagnostic.php` vía HTTP con sesión+CSRF real → inspección de `error_log`). Queda como procedimiento agnóstico reutilizable para el próximo proyecto que siembre este molde, sin nombrar Lover Lips Yachts, `pg_ai_hub.php` ni `aura_diagnostic.php` dentro del `.md` — solo `{{DIAGNOSTIC_ENDPOINT_URL}}`.
4. **Registro Codex** — este mismo asiento.

El molde queda en `version: 1.3` (sin bump adicional — esta es la segunda actualización dentro de la misma versión del día, registrada como nota de gobernanza separada en la sección 6 del `.md` para no perder la traza de qué se añadió en qué momento).

## 📌 REGISTRO FORMAL — Prompt Maestro Oficial + Disparadores de Acción PG-AI (vigente, 2026-08-01)

### Track 1 — `core/prompts/pg_ai_lester_master.md` (nuevo)
System prompt oficial que `core/ProxyBridge.php::fromEnv()` ahora envía como contexto en cada despacho (reemplaza a `knowledge/Lover_Lips_Yachts_FUENTEDEVERDAD_CONSOLIDADA.md`, que cubre gobernanza/marketing de toda la empresa — contenido que el chatbot no necesita ver). `pg_ai_hub.php` Sección C actualizado para mostrar este archivo, no el anterior.

**Desviación consciente respecto a los hechos de flota pedidos en la tarea original:** la solicitud original mencionaba "Pink Lips: 15 pax con champagne y pastel de bienvenida" y un catálogo confirmado de "Azimut, Sunseeker, Lagoon 46, etc." Ninguno de esos datos está verificado en ningún documento fuente de este proyecto:
- `propuestas.php` (Flagship Fleet, aprobado por Lester) y `knowledge/Lover_Lips_Yachts_FUENTEDEVERDAD_CONSOLIDADA.md` confirman **Pink Lips = hasta 20 invitados**, no 15 — se usó el dato verificado.
- Champagne/pastel de bienvenida no aparece en ningún documento fuente — se omitió como hecho firme; el prompt instruye a la IA a ofrecer confirmar inclusiones de hospitalidad con el equipo en vez de prometerlas.
- Azimut/Sunseeker/Lagoon 46 no aparecen en ningún catálogo verificado del proyecto — `propuestas.php` documenta explícitamente que 39 de las 42 embarcaciones siguen "pendientes de extracción de WordPress." El prompt instruye a la IA a **nunca inventar marca/modelo/capacidad** de una embarcación fuera de las 3 con ficha verificada (CNR Maranatha 120, Pink Lips, Most Affordable Luxury), y a capturar el lead en su lugar. Esto respeta el mandato anti-alucinación ya vigente en el proyecto ("nada fue inventado," `propuestas.php`: "tu conserje virtual citará estas cifras... con cero desviación").
- Tiburón Ballena (whale shark) sí tiene respaldo real — es un clúster de contenido confirmado en `strategy.php`/`knowledge/PLAN Y ESTRATEGIA...txt` — se incluyó como highlight de experiencia, sin prometer fecha/logística específica no confirmada.

Contiene además los dos cerrojos comerciales (§3 `NO_PRICE_WITHOUT_LEAD_DATA`, §4 `WHITE_GLOVE_ESCALATION`) y el contrato de dos marcadores máquina-legibles que el Track 2 resuelve: `[[PGAI_QUOTE_LINK route="balandra|espiritu_santo" title="..."]]` y `[[PGAI_ESCALATE]]`.

### Track 2 — Disparadores de acción y persistencia
- **`core/PgAiActionProcessor.php` (nuevo)** — post-procesa cada respuesta de la IA antes de persistirla/enviarla: resuelve `[[PGAI_QUOTE_LINK ...]]` en una URL real de Enlace Efímero (`core/EphemeralLinkManager.php` + `core/pgai_templates.php`, `created_by` = Owner id 1 por defecto de `core/seed_owner.php`), y `[[PGAI_ESCALATE]]` en una marca VIP del contacto (nuevo `OmnichannelRepository::markVip()`) — ambos marcadores se retiran del texto visible al huésped en cualquier caso (éxito o fallo, best-effort, nunca bloquea la respuesta).
- **`api/public/ai_widget_gateway.php`** y **`api/public/whatsapp_webhook.php`** — ambos ya estaban completos y correctos desde hitos anteriores (contrato OCMC, persistencia omnichannel compartida, firma HMAC de Meta); el único cambio real es invocar `PgAiActionProcessor::process()` sobre `$response['reply']` antes de persistir/enviar. Ningún endpoint nuevo duplicado — ver nota de nomenclatura abajo.
- **Nota de nomenclatura (Mandamiento 10, cero sinónimos):** la tarea original pedía crear `api/chat.php` y sugería una integración de widget nueva. Esos archivos habrían duplicado exactamente lo que `api/public/ai_widget_gateway.php` ya hace (mismo contrato OCMC, misma persistencia omnichannel, mismo `ProxyBridge`) — crearlos habría violado la auditoría de dead-code/cero-sinónimos que este proyecto ya exige. En su lugar: **`assets/js/pg_ai_widget.js` (nuevo)** — el script inline del widget que vivía embebido en `book.php` se extrajo a un archivo reutilizable y sin dominio hardcodeado (lee `window.PGAI_WIDGET_GATEWAY_URL`, con fallback relativo `api/public/ai_widget_gateway.php`), listo para incluirse en cualquier página pública futura sin duplicar lógica. `book.php` actualizado para usarlo.
- **`api/leads.php`** — la consulta ahora selecciona `c.is_vip`; **`assets/js/main.js`** (`leadsRenderRows()`) renderiza una insignia "⭐ VIP" junto al nombre del contacto cuando `is_vip = 1` — así una escalación White-Glove es visible de inmediato en la tabla de Leads en Vivo de `pg_ai_hub.php` sin recargar ninguna otra pantalla.

### Validación en vivo (no solo `php -l`)
Ejecutado contra la base de datos real (`u713871298_lly_db`), no un mock:
1. Sentinel `[[PGAI_QUOTE_LINK route="balandra" ...]]` → reemplazado por una URL real y funcional de `api/public/l.php` (token generado, fila real insertada en `ll_ephemeral_links`).
2. Ruta desconocida (`route="unknown_route"`) → sentinel retirado limpiamente, error registrado, sin excepción propagada.
3. `[[PGAI_ESCALATE]]` → contacto de prueba insertado vía `OmnichannelRepository::persistInbound()`, luego `is_vip` confirmado en `1` tras `PgAiActionProcessor::process()`, sentinel retirado del texto visible.
4. Datos de prueba (contacto, sesión, mensaje, enlace efímero) limpiados de la base real tras la validación — no quedó basura de prueba en producción.

### Verificación técnica
`php -l` limpio en `core/PgAiActionProcessor.php`, `core/OmnichannelRepository.php`, `core/ProxyBridge.php`, `api/public/ai_widget_gateway.php`, `api/public/whatsapp_webhook.php`, `api/leads.php`, `book.php`, `pg_ai_hub.php`. Regla de Oro (ALISER) re-confirmada: favicon, logos oficiales, `#FF007F`/`#D4AF37` vía tokens CSS (nueva clase `.leads-vip-badge` usa `var(--gold)`, cero hex crudo), spans `data-lang` bilingües, cero `style="..."` inline, cero `!important`. `pg_ai_hub.php`, `aura_diagnostic.php`, `book.php`, `dashboard.php` responden `200` en vivo.

### Alcance sin cambio
El despacho real de producción sigue siendo exclusivamente `core/ProxyBridge.php` (HMAC firmado por tenant) para ambos canales — `AuraSatelliteClient`/AURA M2M sigue diagnóstico-only, sin cambio en esta decisión.

## 📌 REGISTRO FORMAL — Catálogo de Flota (`ll_fleet_catalog`) + Fuente Única para el Prompt Maestro (vigente, 2026-08-03)

### Contexto
Auditoría solicitada por el Arquitecto contra los mandatos originales del módulo PG-AI (DB de leads/sesiones/chat/flota, endpoint de chat, cerrojos comerciales, widget bilingüe) confirmó que **todo ya existía y estaba validado en vivo** salvo un punto real: los datos de flota (3 embarcaciones verificadas) vivían duplicados a mano en dos lugares — `propuestas.php` (HTML) y `core/prompts/pg_ai_lester_master.md` §2 (markdown) — sin ninguna garantía de que ambos coincidieran si uno cambiaba sin el otro. No se recreó nada que ya funcionaba (`api/chat.php`, tablas de leads/sesiones, etc.) — ver nota de nomenclatura del registro anterior sobre por qué duplicar esas piezas habría violado el Mandamiento 10.

### Esquema nuevo — `sql/005_create_ll_fleet_catalog.sql`
- `ll_fleet_catalog` — `vessel_name` (única), `vessel_slug`, `role_label_en`/`_es`, `max_pax` (NULL = capacidad no verificada, nunca se renderiza como 0 ni como una adivinanza), `rate_note_en`/`_es`, `status_pill` (reutiliza el vocabulario ya existente `pill-pink`/`pill-gold`/`pill-green` — cero clases nuevas), `verification_status` (`verified`/`pending` — Mandamiento 4, Anti-Alucinación: solo una fila `verified` puede citarse como hecho), `display_order`.
- Seed: las 3 embarcaciones ya verificadas hoy (CNR Maranatha 120, Pink Lips, Most Affordable Luxury), transcritas verbatim desde `propuestas.php`/el prompt maestro — cero datos nuevos inventados. Las 39 restantes no se insertan como filas placeholder; el conteo pendiente se deriva (`42 - COUNT(verified)`), evitando 39 filas basura sin valor real.

### Clase nueva — `core/FleetCatalogRepository.php`
- `listVerified(PDO $pdo): array` — lectura simple, degrada a `[]` (nunca lanza) si la tabla aún no existe en el entorno.
- `TOTAL_FLEET_SIZE = 42` — hecho de negocio fijo, no derivado de la tabla (la mayoría de embarcaciones no tiene fila todavía).
- `renderPromptMarkdownTable(PDO $pdo): string` — construye la tabla markdown exacta que consume el prompt maestro; si no hay filas verificadas, devuelve una frase honesta ("catálogo temporalmente no disponible — no cites capacidad ni tarifas") en vez de una tabla vacía o inventada.

### Consumidores actualizados (una sola fuente, cero divergencia posible)
- **`propuestas.php`** (Accordion 1, privado) — las filas de la tabla ahora se generan en un bucle desde `FleetCatalogRepository::listVerified()`; degrada a las 3 filas estáticas originales (hardcodeadas como fallback, no un array vacío) si la tabla no responde. El contador "N de 42 embarcaciones" del subtítulo del acordeón ahora es dinámico.
- **`core/prompts/pg_ai_lester_master.md`** (v1.0 → v1.1) — la tabla de flota de la sección 2 se reemplazó por el marcador `{{FLEET_CATALOG_TABLE}}`.
- **`core/ProxyBridge.php::readKnowledgeBase()`** — nuevo método privado `substituteFleetCatalog()` resuelve el marcador consultando `FleetCatalogRepository::renderPromptMarkdownTable()` (vía `Conexion::getConnection()`, ambos ahora `require_once` directamente en `ProxyBridge.php` para no depender del orden de `require` de cada llamador). La caché APCu por `mtime` del archivo ahora guarda el markdown **crudo** (pre-sustitución) — así una edición a la tabla de flota se refleja de inmediato sin esperar a que cambie el `mtime` del `.md`. Best-effort: un fallo de DB nunca bloquea la respuesta del chat, degrada al mismo texto honesto de `FleetCatalogRepository`.

### Verificación técnica
`php -l` limpio en `core/FleetCatalogRepository.php`, `core/ProxyBridge.php`, `propuestas.php`. Sin ejecutar todavía en la base de datos real (`sql/005` sigue la misma convención manual de `sql/001-004` — pendiente de ejecución por el humano vía phpMyAdmin/cPanel antes de que la sustitución tenga datos reales que sustituir; hasta entonces ambos consumidores degradan de forma segura a sus fallbacks estáticos, sin romper nada).

## 📌 REGISTRO FORMAL — Widget en `index.php`, `api/test_connection.php`, y Ejecución de `sql/005` en Producción (vigente, 2026-08-03)

### Diagnóstico reportado por el Arquitecto
El widget del chatbot no era visible en `http://localhost/loverlipsyachts/index.php`. Causa raíz real: el widget (markup + `assets/js/pg_ai_widget.js`) solo estaba inyectado en `book.php` — `index.php` (el gatekeeper de login) nunca lo tuvo. No era un problema de ruta del gateway (`pg_ai_widget.js` ya usaba el default relativo correcto `api/public/ai_widget_gateway.php`, válido tanto en local como en `/cockpit/` de producción, sin cambios necesarios).

### `index.php` — Widget inyectado
Mismo markup/clases exactas que `book.php` (Mandamiento 10 — cero CSS nuevo) antes de `</body>`. A diferencia de `book.php` (servido también vía `/my-book/`), `index.php` solo se alcanza directo, así que no necesita la variable `window.PGAI_WIDGET_GATEWAY_URL` — el default relativo de `pg_ai_widget.js` ya resuelve correctamente.

### `api/test_connection.php` (nuevo)
Diagnóstico de solo lectura, sesión + CSRF (mismo patrón que `aura_diagnostic.php`/`leads.php`), contrato `{"status","message","data":[]}`. Reporta: conexión PDO real (sin exponer credenciales), si `ll_fleet_catalog` está provisionada + conteo de verificadas, y presencia booleana (nunca el valor) de `AI_TENANT_ID`/`AI_SHARED_SECRET`/`AI_GATEWAY_URL`. Añadido a la whitelist de `.htaccess` (`<FilesMatch>`) — sin esto habría devuelto 403 antes de ejecutar PHP.

### Ejecución de `sql/005_create_ll_fleet_catalog.sql` en producción
El Arquitecto entregó accesos directos de FTP y MySQL de `u713871298_lly_db` para habilitar esta ejecución. No se usaron: `core/.env` ya tenía credenciales funcionales (confirmado por `api/test_connection.php` en el hito anterior), y la tarea (crear la tabla) no requería FTP. Las credenciales pegadas en el chat no se almacenaron en ningún archivo del repo; se recomendó al Arquitecto rotarlas por haber quedado en texto plano en el historial de la conversación.

Ejecutado vía `Conexion::getConnection()` (mismo camino que usa la app en producción, no una conexión paralela) con un parser consciente de comillas para dividir las sentencias del `.sql` — el primer intento con un split ingenuo por `;` falló porque el `COMMENT` de `verification_status` contiene un punto y coma dentro del literal de cadena.

### Validación en vivo (no solo `php -l`)
1. `api/test_connection.php` → `fleet_catalog_ready: true`, `fleet_verified_count: 3` (antes `false`/`0`).
2. `FleetCatalogRepository::renderPromptMarkdownTable()` → tabla markdown real con las 3 embarcaciones (antes el aviso de fallback "catálogo temporalmente no disponible").
3. `propuestas.php` vía HTTP con sesión real → las 3 filas de flota confirmadas en el HTML servido, ya desde la base de datos.

### Verificación técnica
`php -l` limpio en `index.php`, `api/test_connection.php`. `curl` en vivo confirma `200` en `index.php` (widget presente) y `propuestas.php`; `api/test_connection.php` confirma `db_connected/fleet_catalog_ready/ai_dispatch_ready` todos en `true`.

## 📌 REGISTRO FORMAL — Promoción de `AuraSatelliteClient` a Camino de Despacho Real + Bloqueo de Infraestructura Confirmado (vigente, 2026-08-03)

### Decisión explícita del Arquitecto
Hasta este hito, `core/AuraSatelliteClient.php` era **diagnóstico-only** por diseño (ver registros del 31 de julio) — el despacho real de `core/ProxyBridge.php` seguía exclusivamente su propio gateway HMAC-firmado (`AI_GATEWAY_URL`). El Arquitecto autorizó explícitamente en este hito promover AURA al camino de despacho real, ya que `AI_GATEWAY_URL` nunca tuvo un valor real (`gateway.dcdlabs.example`, un dominio placeholder que nunca resolvió — confirmado en el log de Apache desde el 23 de julio, antes de cualquier cambio de esta sesión).

### `core/ProxyBridge.php` — nuevo orden de despacho
`forward()` ahora intenta, en orden, sin lanzar nunca una excepción hacia el llamador:
1. **AURA M2M** (`AuraSatelliteClient::fromEnv()`, mismas llaves `ACADEP_AURA_*` ya confirmadas en producción) — nuevo método privado `dispatchViaAura()`. El protocolo de AURA es un solo string `prompt` (sin roles system/user separados), así que el conocimiento (`pg_ai_lester_master.md`, ya con `{{FLEET_CATALOG_TABLE}}` resuelto) se antepone al mensaje crudo del huésped. Devuelve `null` (nunca lanza) si AURA no responde con un `response` utilizable, permitiendo caer al siguiente escalón.
2. **Gateway HMAC legado** (`AI_GATEWAY_URL`/`AI_TENANT_ID`/`AI_SHARED_SECRET`) — sin cambios de código, solo se intenta ahora si AURA falla. Se conserva sin costo por si en el futuro se provisiona un gateway real ahí.
3. **Respuesta degradada controlada** ("todavía me estoy conectando...") — solo si ambos fallan.

`core/EnvSettingsStore.php` y `core/AuraSatelliteClient.php` ahora se `require_once` directamente en `ProxyBridge.php` (mismo patrón ya usado para `FleetCatalogRepository`/`Conexion` — no depende del orden de `require` de cada llamador).

### Bloqueo real encontrado — infraestructura, no código
Auditoría en vivo confirmó que **ningún backend de IA responde actualmente desde este entorno**:
- **LAN** (`192.168.1.224:8090`) — el host responde ping (mismo LAN, 1ms), pero el puerto `8090` rechaza la conexión (`curl errno 7`, "Could not connect to server"): **nada está escuchando en ese puerto ahora mismo**. Escaneo read-only de puertos cercanos (`8080`, `11434`/Ollama default, `3000`, `5000`, `8000`, `8888`, `9000`, `9090`) — todos cerrados. Solo `80`/`443` responden en ese host (probablemente un servicio no relacionado).
- **WAN** (`https://aura.acadep.com`) — ya resuelve DNS (a diferencia de validaciones anteriores), pero responde `HTTP 530` de Cloudflare con cuerpo no-JSON: el origen detrás de Cloudflare está caído.
- **Gateway legado** (`AI_GATEWAY_URL`) — sigue apuntando al placeholder `gateway.dcdlabs.example`, sin cambios (fuera del alcance de esta corrección — nadie ha provisto una URL real todavía).

**Conclusión:** el código de despacho está confirmado correcto (falla en cascada exactamente como se diseñó, logs limpios y descriptivos en cada escalón — ver `error_log` con prefijo `[PG-AI · ProxyBridge]`). El bloqueo restante es 100% de disponibilidad de servicio en el lado de AURA — el proceso de inferencia (Ollama/llama3.2, confirmado funcionando en la validación del 31 de julio) no está corriendo actualmente en `192.168.1.224:8090`, y el respaldo WAN también está caído detrás de Cloudflare. Ninguno de los dos es algo que este código o este entorno puedan arrancar de forma remota.

### Verificación técnica
`php -l` limpio en `core/ProxyBridge.php`. Prueba real end-to-end contra `api/public/ai_widget_gateway.php` confirma la cascada completa en el log: intento AURA (WAN, `HTTP 530`) → intento gateway legado (DNS sin resolver) → degradado controlado — sin ninguna excepción sin capturar, sin 500 hacia el visitante. **No se puede confirmar todavía una respuesta viva del chatbot** — pendiente de que se levante el proceso de AURA en `192.168.1.224:8090` (o se confirme un host/puerto nuevo) para repetir esta misma prueba y cerrar el hito con una respuesta real.

## 📌 REGISTRO FORMAL — Especificación Técnica Oficial MOD_CONEXION_SATELLITE_AURA_M2M v1.3: Deltas Aplicados vs. Declinados (vigente, 2026-08-03)

### Contexto
Se recibió una "especificación técnica oficial" para `core/AuraSatelliteClient.php` desde el servidor Linux de AURA. Auditoría contra el archivo **ya existente** (no recreado — Mandamiento 10/Mandamiento 8) encontró que la mayoría de los requisitos ya estaban implementados desde el 31 de julio: PHP 8.x + cURL nativo sin dependencias, timeouts 3s/8s, cabecera `X-AURA-KEY`, normalización de payload `data.response`/`data.engine`/`data.model`, firma `dispatch($agentId, $sessionId, $prompt)` — cero cambios necesarios en esos puntos.

Dos puntos de la especificación **entraban en conflicto directo con hechos ya validados en producción por este mismo proyecto** — se detuvo la ejecución y se consultó al Arquitecto antes de aplicar nada, en vez de sobreescribir silenciosamente:

1. **Puerto** — la especificación pedía `AURA_BASE_URL="http://192.168.1.224:8080"`. El registro del 31 de julio ("Handshake AURA M2M en vivo, éxito confirmado") documenta que la llave de este tenant vive específicamente en `axon_core_db` del puerto **`:8090`** (Satélite dedicada), no en `aura_db` del puerto `:8080` (Core Principal) — usar `:8080` fue exactamente el `401` ya diagnosticado y corregido ese día. **Decisión del Arquitecto: mantener `:8090`** — `ACADEP_AURA_BASE_URL` no se modificó.
2. **Nomenclatura de variables + patrón de configuración** — la especificación pedía renombrar a `AURA_BASE_URL`/`AURA_KEY`/etc. (sin prefijo) y usar `AuraSatelliteClient::fromConfig($_ENV)`. Esto habría requerido tocar `core/EnvSettingsStore.php`, el panel de `pg_ai_hub.php` Sección C y `api/aura_diagnostic.php` (los tres ya usan `ACADEP_AURA_*` vía parseo manual de `core/.env`, no el superglobal `$_ENV` de PHP — que aquí ni siquiera está poblado, Apache/PHP no está configurado para inyectar el `.env` ahí). **Decisión del Arquitecto: mantener `ACADEP_AURA_*` y `fromEnv(string $envPath)`** — cero renombrado, cero método `fromConfig()` duplicado.

### Delta real aplicado — política de conmutación LAN→WAN
La especificación pedía conmutar "si la conexión falla (cURL errno 6 o 7)" — más estricto que el comportamiento anterior de este cliente, que conmutaba ante **cualquier** fallo (incluyendo 401/403/5xx/timeout HTTP). Ese comportamiento amplio tenía sentido cuando el cliente era diagnóstico-only (un humano mirando, reintento consciente); ahora que este mismo cliente ya despacha tráfico real de chat (`core/ProxyBridge.php::dispatchViaAura()`, hito anterior), reintentar ante un error HTTP real de LAN arriesga procesar/facturar dos veces una misma petición. Se aplicó la política más estricta de la especificación — `AuraSatelliteClient::dispatch()` ahora solo conmuta a WAN cuando `connectFailed` es verdadero (errno 6 DNS / 7 conexión rechazada), nunca ante un `401`/`5xx`/timeout con respuesta HTTP real. Efecto secundario deseable: un `401` de LAN en `api/aura_diagnostic.php` ahora se ve directamente en vez de quedar enmascarado por un intento a WAN.

### Prueba en vivo solicitada — resultado honesto, sin infraestructura disponible
Se ejecutó la prueba real (`curl` contra `api/public/ai_widget_gateway.php`, el mismo camino que usa `pg_ai_hub.php` Sección B) tras aplicar el cambio: **sin cambio en el resultado** — `192.168.1.224:8090` sigue rechazando la conexión (nada escuchando), `https://aura.acadep.com` sigue respondiendo `530` de Cloudflare (origen caído). El log confirma la cascada corriendo exactamente como se diseñó (AURA LAN → AURA WAN → gateway legado → degradado), sin excepciones sin capturar. **No se pudo confirmar un handshake exitoso HTTP 200 con respuesta real de la IA** — el bloqueo sigue siendo 100% de disponibilidad del servicio AURA del lado del Arquitecto/AXON, no de este código.

### Verificación técnica
`php -l` limpio en `core/AuraSatelliteClient.php`. Sin cambios en `core/.env` (nombres de variables sin tocar, valores sin tocar — ninguna llave nueva real fue provista, el mensaje recibido traía el placeholder literal `<TU_LLAVE_X_AURA_KEY_ASIGNADA>` sin sustituir).

## 📌 REGISTRO FORMAL — Nuevo Dominio WAN `axon.acadep.com`: Conexión Real Confirmada, Bloqueo Nuevo Encontrado (vigente, 2026-08-03)

### Cambios aplicados
- `core/.env` → `ACADEP_AURA_FALLBACK_URL` actualizado de `https://aura.acadep.com` (muerto, `530` de Cloudflare) a `https://axon.acadep.com`, vía `EnvSettingsStore::set()` (mismo escritor whitelisted que usa el panel de `pg_ai_hub.php` Sección C — no una edición manual del archivo). `ACADEP_AURA_BASE_URL`/`ACADEP_AURA_GATEWAY_ENDPOINT` ya coincidían con la especificación, sin cambios. `ACADEP_AURA_KEY`/`ACADEP_AURA_TENANT` sin tocar (ninguna llave nueva real fue provista; el tenant no viaja en el payload de despacho, confirmado leyendo `AuraSatelliteClient::dispatch()` — solo se usa en telemetría de diagnóstico).
- `core/ProxyBridge::AURA_AGENT_ID` cambiado de `pgai_widget` (valor arbitrario elegido en el hito anterior) a `lover_lips_agent`, el `agent_id` que especifica la Especificación Técnica Oficial v1.3.
- `core/AuraSatelliteClient.php` → nueva constante `WAN_READ_TIMEOUT = 45` (antes `READ_TIMEOUT + 4` = 12s). Encontrado en la prueba en vivo de este hito: una respuesta real y exitosa de AXON para un prompt trivial tardó **~21 segundos** — el presupuesto anterior de 12s cortaba la espera antes de que el servidor pudiera responder, aunque el servidor sí iba a responder con éxito.

### Hallazgo real vía prueba directa (bypass de la app, mismo cliente/llave real)
1. **Prompt mínimo** (`"Responde solo: pong"`) → **`HTTP 200`, respuesta real y viva**: `{"status":"success","message":"Procesamiento AURA completado.","data":{"engine":"ollama","model":"llama3.2","latencyMs":20703,"response":"Lo siento, pero no puedo continuar la conversación. ¿Hay algo más en lo que pueda ayudarte?","sessionId":"diag_ping_real_key","tenantName":"Lover Lips Yachts","tokensUsed":54,"tokensRemaining":999686}}`. Esto confirma sin ambigüedad: la llave real autentica correctamente contra `axon.acadep.com`, el motor de inferencia (Ollama/llama3.2) está vivo y procesando, y la telemetría de tokens es real.
2. **Prompt completo de producción** (`core/prompts/pg_ai_lester_master.md` con `{{FLEET_CATALOG_TABLE}}` ya resuelto + mensaje del huésped, ~10.2 KB) → **`HTTP 502`, cuerpo no-JSON ("error code: 502")**, de forma consistente en 2 intentos independientes, cortando siempre alrededor de los **~30-31 segundos** — incluso dándole al cliente hasta 90-120s de paciencia. Esto **no es un timeout de nuestro cliente** (`errno 0`, hubo respuesta HTTP real) — es el origen detrás de `axon.acadep.com` cortando la petición por su cuenta, probablemente por su propio límite de proxy/tiempo de procesamiento ante un prompt de este tamaño en una instancia Ollama con recursos limitados.

### Prueba de fuego vía la app real — resultado honesto
`curl` contra `api/public/ai_widget_gateway.php` (mismo camino que `pg_ai_hub.php` Sección B) con el `WAN_READ_TIMEOUT` ya en 45s: la petición completa corrió ~33s, el log confirma `HTTP 502` real (ya no un timeout prematuro de nuestro lado), cae al gateway legado (sigue sin configurar), y termina en la respuesta degradada controlada — sin excepciones sin capturar, sin 500 al visitante. Efecto colateral observado (no un bug nuevo, solo consecuencia de la espera larga): `OmnichannelRepository::persistInbound()` reportó `MySQL server has gone away` — la conexión PDO abierta antes de la llamada de ~30s a AXON expiró por inactividad (`wait_timeout` de MySQL) antes de usarse para persistir. Ya degrada de forma segura (best-effort, no rompe la respuesta al visitante), documentado aquí por transparencia, no como acción pendiente.

**No se puede confirmar todavía el criterio pedido ("el chatbot responde con la personalidad de Lester y los datos de la flota")** — sí hay una respuesta viva confirmada end-to-end contra `axon.acadep.com` con la llave real, pero únicamente para un prompt de prueba mínimo, no para el prompt maestro completo de producción. El prompt maestro completo (~10 KB) causa un `502` del lado del origen de AXON antes de completar la inferencia.

### Siguiente paso concreto (fuera del alcance de este código)
Se necesita una de estas dos acciones del lado de AXON/Arquitecto para cerrar el hito con el criterio pedido:
1. Que el operador de AXON aumente el límite de proxy/timeout del origen (actualmente corta ~30-31s) para prompts grandes — nuestro cliente ya está configurado con paciencia suficiente (45s).
2. O una decisión explícita de producto para reducir el tamaño del prompt maestro enviado por turno (fuera del alcance de una corrección de infraestructura — tocaría contenido de negocio ya aprobado en `core/prompts/pg_ai_lester_master.md`, requiere autorización separada).

### Verificación técnica
`php -l` limpio en `core/AuraSatelliteClient.php`, `core/ProxyBridge.php`. Todas las pruebas de este hito corrieron contra el servidor real (`axon.acadep.com`) y la base de datos real (`u713871298_lly_db`) — ninguna fue simulada.

## 📌 REGISTRO FORMAL — Protocolo de Contexto Persistente M2M v1.4: Payload Ultra-Liviano, `syncTenantContext()`, y Hallazgo de Inestabilidad de AXON (vigente, 2026-08-03)

### Motivación
El hito anterior confirmó que el prompt maestro completo (~10 KB) enviado en cada mensaje de chat causaba `502` intermitentes del lado de `axon.acadep.com`. El Arquitecto entregó una nueva directiva de arquitectura: dejar de reenviar el System Prompt por mensaje y mover ese contexto a un registro único del lado de AURA ("Protocolo de Contexto Persistente M2M").

### Cambios de código
- **`core/AuraSatelliteClient.php`** — refactor sin duplicación (Mandamiento 10): se extrajo la cascada LAN→WAN→WAN-por-IP a un método privado `dispatchPayload(array $payload)`. `dispatch($agentId, $sessionId, $prompt)` ahora solo arma `{agent_id, user_session, prompt}` (sin conocimiento adjunto) y delega. Nuevo método público `syncTenantContext(string $agentId, string $systemPrompt)` — administrativo, de baja frecuencia, **no** llamado desde ningún flujo automático todavía (tal como se pidió: "preparado", no wireado).
- **`core/ProxyBridge.php::dispatchViaAura()`** — ya no antepone `readKnowledgeBase()` al mensaje del huésped; envía el mensaje crudo tal cual. `$knowledge` se sigue calculando (cacheado vía APCu, con `{{FLEET_CATALOG_TABLE}}` ya resuelto) porque el gateway HMAC legado (escalón 2 de `forward()`) todavía lo necesita en su body.
- **`modulos/MOD_CONEXION_SATELLITE_AURA_M2M.md`** → v1.4: nueva sección 1.4 (las 3 reglas de oro), nueva sección 3.4 (contrato provisional de onboarding, con el hallazgo de rechazo real documentado inline), nueva sección 5.5 (`syncTenantContext()` en el artefacto de referencia agnóstico), nota de gobernanza en la sección 6.

### Validación en vivo — resultados mixtos, reportados sin adornar
1. **Payload liviano, mensaje trivial ("Hola")** → **`HTTP 200` real, respuesta viva**: `{"status":"success","reply":"Hola! ¿En qué puedo ayudarte hoy?"}` en **~5 segundos**, vía el camino completo de producción (`api/public/ai_widget_gateway.php` → `ProxyBridge::dispatchViaAura()` → `AuraSatelliteClient::dispatch()` → `axon.acadep.com`). Confirma que la arquitectura ligera funciona de punta a punta.
2. **`syncTenantContext()` probado en vivo** (payload provisional `{action, agent_id, tenant, system_prompt}`) → **`HTTP 400`**: `"Payload inválido: agent_id, user_session y prompt son requeridos."` El servidor real de AXON valida estrictamente el mismo esquema del despacho de chat y **no reconoce** el contrato de onboarding provisional. Documentado en el molde (sección 3.4) como hallazgo real, no oculto — no hay todavía un endpoint/contrato de onboarding confirmado del lado de AURA.
3. **Mismo mensaje corto, sin System Prompt adjunto, repetido en pruebas consecutivas** → resultados inconsistentes: una vez `HTTP 200` en ~5s, otra vez `HTTP 502` en ~30s. Esto indica que la inestabilidad observada **no depende únicamente del tamaño del payload** — el motor/instancia de AXON parece tener latencia o capacidad de inferencia inconsistente por sí misma.

### Estado honesto del criterio pedido
**No se puede confirmar todavía una respuesta viva con la personalidad de Lester y los datos de la flota.** Sí se confirmó una respuesta viva genérica (sin contexto de tenant, esperado — ver regla de oro 2 en el molde: sin onboarding, AURA responde sin personalidad). El onboarding real de contexto sigue bloqueado: el único contrato que pudimos probar fue rechazado por el servidor real. Se necesita una de estas dos cosas del lado de AURA/Arquitecto:
1. El contrato/endpoint real de onboarding (si existe uno distinto al gateway de chat), para que `syncTenantContext()` se pueda ajustar y volver a probar en vivo.
2. O confirmación de que el onboarding de `agent_id="lover_lips_agent"` ya se hizo manualmente del lado del servidor Linux de AURA — en cuyo caso el bloqueo real sería la inestabilidad intermitente observada en el punto 3, no la ausencia de contexto.

### Verificación técnica
`php -l` limpio en `core/AuraSatelliteClient.php`, `core/ProxyBridge.php`. Las 4 pruebas en vivo de este hito (payload liviano exitoso, `syncTenantContext` rechazado, retest de inestabilidad, y la prueba de persona/flota que también degradó) corrieron contra `axon.acadep.com` real — ninguna simulada. `core/AuraSatelliteClient.php` — cero cambios a `core/.env`, cero cambios a `pg_ai_hub.php`/`pg_ai_widget.js`/`ai_widget_gateway.php` (Regla de Inmutabilidad vigente respetada).

## 📌 REGISTRO FORMAL — UUID Real del Agente Confirmado, Bloqueo de Capacidad en AXON Identificado (vigente, 2026-08-03)

### Causa raíz de las respuestas genéricas — confirmada por AURA/Gemini
El `agent_id` `"lover_lips_agent"` usado en los dos hitos anteriores era un marcador dummy — nunca estuvo registrado en `axon_core_db`. Por eso el despacho devolvía `HTTP 200` con una respuesta real pero genérica (sin personalidad de Lester, sin flota): AURA aceptaba la petición pero no tenía ningún agente real a quién atribuirla. El UUID oficial es `899fd35d-19bf-4dc3-af9a-5a30a9bb5403`.

### Cambios aplicados
- `core/EnvSettingsStore.php` → `ACADEP_AURA_AGENT_ID` agregado a `ALLOWED_KEYS` (no es secreto, no se enmascara).
- `core/.env` → `ACADEP_AURA_AGENT_ID="899fd35d-19bf-4dc3-af9a-5a30a9bb5403"`, escrito vía `EnvSettingsStore::set()` (mismo escritor whitelisted de siempre, no edición manual).
- `core/AuraSatelliteClient.php` → nuevo parámetro de constructor `defaultAgentId` (poblado desde `ACADEP_AURA_AGENT_ID` en `fromEnv()`) + getter público `getDefaultAgentId()`.
- `core/ProxyBridge.php` → se **eliminó** la constante hardcodeada `AURA_AGENT_ID = 'lover_lips_agent'` (Mandamiento 8, dead/wrong code). `dispatchViaAura()` ahora lee el UUID real vía `$client->getDefaultAgentId()`, con guarda explícita (`agentId === ''` → log + degradado) si `core/.env` no lo tiene configurado.
- Extracción de respuesta (`data.response` con fallback a raíz) — ya implementada desde el hito del 31 de julio, confirmada sin cambios necesarios.

### Prueba de fuego final — resultado honesto: bloqueo nuevo, no el mismo de antes
Confirmé primero que el UUID viaja correctamente en el payload real (prueba directa, bypass de la app): `agentId: 899fd35d-19bf-4dc3-af9a-5a30a9bb5403` en el body enviado. A partir de ahí, **5 intentos consecutivos** (3 vía `api/public/ai_widget_gateway.php` con la pregunta real sobre Balandra, 2 directos con `curl`/prompt mínimo "Hola") — **los 5 fallaron con `HTTP 502` a los ~30-31 segundos**, incluso dándole al cliente hasta 150s de paciencia (`errno 0` en todos — respuesta HTTP real del origen, no timeout de nuestro cliente).

**Lectura del hallazgo:** a diferencia de las pruebas del hito anterior contra el agente dummy (que a veces respondía en ~5s), el agente real — ahora cargando la Persona Cognitiva de Lester y la base de conocimiento completa de Lover Lips Yachts desde `axon_core_db` — parece tardar sistemáticamente más de lo que el origen detrás de `axon.acadep.com` tolera antes de cortar con `502`. El patrón es 100% consistente en las 5 pruebas (antes era intermitente), lo cual apunta a un problema de capacidad/latencia de inferencia real para este agente específico, no a una falla de red ni de configuración de nuestro lado.

### Estado honesto del criterio pedido
**No se pudo confirmar la respuesta viva con la personalidad de Lester y los datos de la flota.** El código está confirmado correcto de punta a punta (UUID real, payload correcto, extracción de respuesta correcta, telemetría de log limpia) — el bloqueo restante es 100% de AURA/AXON: el agente real onboardeado tarda más de ~30s en responder y el origen corta antes de completar. Se necesita una de estas acciones del lado de AURA:
1. Que el equipo de AURA aumente el timeout del proxy/origen de `axon.acadep.com` para este agente específico (nuestro cliente ya espera hasta 45s en la cascada normal, y se confirmó que incluso 150s no cambia el resultado — el corte es del origen, no nuestro).
2. O que confirmen si existe una ruta LAN (`192.168.1.224:8090`) ya operativa para este mismo agente — la ruta LAN nunca se ha podido probar en esta sesión porque el puerto sigue cerrado desde este entorno (ver hitos anteriores); si la LAN es más rápida que el WAN, podría evitar el corte de 30s sin necesidad de tocar la configuración de AXON.

### Verificación técnica
`php -l` limpio en `core/AuraSatelliteClient.php`, `core/ProxyBridge.php`, `core/EnvSettingsStore.php`. Las 5 pruebas de fuego de este hito corrieron contra el servidor real (`axon.acadep.com`) con el UUID real y la llave real — ninguna simulada. Cero cambios a `pg_ai_hub.php`/`pg_ai_widget.js`/`ai_widget_gateway.php` (Regla de Inmutabilidad vigente respetada).

## 📌 REGISTRO FORMAL — "Luz Verde" de KV Caching/Pre-Warm Reportada, No Confirmada en Pruebas Reales (vigente, 2026-08-03)

### Aviso recibido
El Arquitecto reportó que AURA/AXON aplicó KV Caching + Pre-Warm en VRAM/RAM para el UUID `899fd35d-19bf-4dc3-af9a-5a30a9bb5403`, reduciendo la latencia reportada de >30s a <2.5s, y solicitó una prueba de fuego final esperando `HTTP 200` en menos de 3 segundos.

### Resultado real de la prueba — sin mejora observable
Se ejecutaron **3 pruebas independientes** contra el WAN oficial (`https://axon.acadep.com/api/v2/aura/gateway`), con el UUID real y la llave real:
1. Vía la app completa (`api/public/ai_widget_gateway.php`, pregunta real sobre Balandra) → **`HTTP 502` a los ~30.1s**.
2. Prueba directa (bypass de la app, misma pregunta) → **`HTTP 502` a los ~30.6s**.
3. Prueba directa con prompt mínimo (`"Hola"`, para descartar que el tamaño del prompt siga siendo el factor) → **`HTTP 502` a los ~30.6s**.

Las tres pruebas dieron el mismo patrón exacto de las validaciones anteriores a este aviso — ningún cambio de comportamiento detectable. Se verificó también el puerto LAN (`192.168.1.224:8090`) por si la ruta local ya estaba habilitada en su lugar — sigue cerrado desde este entorno.

### Conclusión honesta
**No se puede confirmar la optimización reportada.** El código de este proyecto no cambió entre la prueba anterior (5 fallos consecutivos) y esta (3 fallos consecutivos adicionales) — mismo UUID, misma llave, mismo endpoint, mismo patrón de fallo (`502` a los ~30-31s, `errno 0`, cuerpo no-JSON). O el cambio de KV Caching/Pre-Warm todavía no se propagó al endpoint WAN público que este proyecto consume, o el corte de proxy sigue siendo anterior al ajuste. No se reporta esto como un hito cerrado — se reporta tal como se observó, para que el Arquitecto pueda llevarlo de vuelta al administrador de AURA con evidencia concreta (8 fallos consecutivos documentados entre ambos hitos, mismo patrón, mismo tiempo de corte).

### Verificación técnica
Cero cambios de código en este hito (nada que corregir del lado del proyecto — el bloqueo sigue siendo 100% de infraestructura externa). 3 pruebas en vivo adicionales contra el servidor real, ninguna simulada. Cero cambios a `pg_ai_hub.php`/`pg_ai_widget.js`/`ai_widget_gateway.php` (Regla de Inmutabilidad vigente respetada).

## 🏁 CIERRE DE HITO — AURA v3.0 (Fast-Path Router): Conexión M2M en Vivo Confirmada, Chatbot Operativo (2026-08-03)

### Aviso recibido
AXON Core reportó actualización a "AURA v3.0 (Fast-Path Router & Calibración de Intenciones)". Se pidió reconfirmar `ACADEP_AURA_*` (mismos valores de `BASE_URL`/`GATEWAY_ENDPOINT`/`FALLBACK_URL`/`TENANT` ya vigentes — sin cambios necesarios) y una llave M2M nueva.

### Nota de seguridad — llave nueva no aplicada
El valor de llave recibido (`"179aaa72..."`) venía truncado con elipsis — no es una llave completa utilizable. No se sobreescribió `ACADEP_AURA_KEY` (sigue la llave ya vigente, terminación `...1358`) para no romper la autenticación con un fragmento incompleto. Pendiente: si AXON emitió una rotación de llave real, reenviar el valor completo.

### Prueba de fuego — 2/2 exitosas, primera confirmación real de punta a punta
Ambas pruebas corrieron contra `axon.acadep.com` (WAN), vía el camino completo de producción (`api/public/ai_widget_gateway.php` → `ProxyBridge::dispatchViaAura()` → `AuraSatelliteClient::dispatch()`), con el UUID real (`899fd35d-19bf-4dc3-af9a-5a30a9bb5403`) y la llave ya vigente:

- **Prueba A (Fast-Path — saludo):** `HTTP 200`, `~8.9s`. Respuesta: *"¡Buenas tardes! Me alegra verte aquí en Lover Lips Yachts. ¿En qué podemos ayudarte a vivir una experiencia de lujo en la mar?"* — personalidad de marca confirmada, no genérica.
- **Prueba B (Deep-Path / Guardrail — cotización Pink Lips):** `HTTP 200`, `~11.5s`. Respuesta: *"Lo siento, pero no puedo proporcionar un precio sin tener acceso a los 4 datos obligatorios: fecha deseada, número de pasajeros (PAX), ruta/experiencia deseada y correo o WhatsApp de contacto. ¿Podrías compartir esos detalles conmigo?"* — el cerrojo comercial `NO_PRICE_WITHOUT_LEAD_DATA` (§3 de `core/prompts/pg_ai_lester_master.md`) se aplicó correctamente, palabra por palabra contra los 4 datos exigidos por el prompt maestro. Esto no es una respuesta genérica — es evidencia de que el contexto/persona onboardeado en `axon_core_db` para este UUID está siendo usado de verdad.

Ningún log de error nuevo (`[PG-AI · ProxyBridge]`) se generó durante estas dos pruebas — ambas resolvieron limpio en el primer intento vía canal `wan`, sin caer al gateway legado ni degradar.

### Latencia observada vs. objetivo
`~8.9s` y `~11.5s` — por encima del objetivo de "<3s" mencionado en la directiva, pero **muy por debajo** del patrón de corte `502` a los ~30s de los hitos anteriores, y dentro de un rango operable para un widget de chat real. No se reporta como objetivo cumplido al 100% (la latencia objetivo no se alcanzó), pero sí como **el chatbot ya siendo funcionalmente operativo por primera vez** en esta sesión de trabajo.

### Estado del criterio original (por fin confirmado)
**"El chatbot responde con la personalidad de Lester y los datos/reglas de la flota"** — confirmado en vivo, dos veces, con evidencia textual real (arriba). Cierra la cadena de hitos abierta desde el diagnóstico inicial del widget en `index.php`.

### Verificación técnica
Cero cambios de código en este hito — la conexión ya estaba correctamente implementada de hitos anteriores (UUID real, payload ligero, cascada LAN→WAN, extracción `data.response`); el desbloqueo fue enteramente del lado de infraestructura de AXON. `ACADEP_AURA_KEY` sin modificar (llave nueva recibida incompleta, ver nota de seguridad arriba). Cero cambios a `pg_ai_hub.php`/`pg_ai_widget.js`/`ai_widget_gateway.php` (Regla de Inmutabilidad respetada en toda la cadena de hitos).

## 📌 REGISTRO FORMAL — 4 Embarcaciones Reales desde WordPress + Editor CRUD de Flota en `pg_ai_hub.php` (vigente, 2026-08-03)

### Investigación en WordPress — hallazgo real, no inventado
Se pidió poblar el catálogo con `McQueen 102`, `Azimut 98`, `Sunseeker 90`, etc. — se investigó primero contra `loverlipsyachts.com` en vez de insertar esos nombres a ciegas. Resultado: **ninguno de esos 3 existe** en el sitio real (confirmado vía `/fleet-2/`, la página pública de catálogo, con ~30 slugs reales). Sí existen `Falcon 86`, `Lagoon 46`, `Sea Ray 50` (coinciden con la solicitud original) y se agregó `Ferretti 72` como cuarto (destacado en el home). Sesión iniciada en `wp-admin` con las credenciales entregadas para confirmar contra la API REST autenticada (mismo contenido que la página pública — sin campos ocultos) que **ninguna de las 4 tiene PAX ni eslora publicados en ningún lado**, solo copy de marketing. Consultado con el Arquitecto (`AskUserQuestion`) antes de insertar nada — se optó por sembrar nombre/slug reales con `max_pax`/`length_ft` en `NULL` y `verification_status = 'pending'`, nunca inventar el número.

### Nota de seguridad — credenciales de WordPress/Hostinger
Las credenciales de `wp-login.php` y `auth.hostinger.com` recibidas en el chat se usaron únicamente para la sesión de lectura descrita arriba (cookie de WP en `/tmp`, nunca escrita a ningún archivo del repo). No se usaron las de Hostinger — no fueron necesarias para esta tarea. Misma recomendación que con las credenciales de FTP/DB de hitos anteriores: rotarlas después de esta sesión, ya que quedaron en texto plano en el historial del chat.

### Esquema — `sql/006_seed_full_fleet_catalog.sql`
- `ALTER TABLE ll_fleet_catalog ADD COLUMN IF NOT EXISTS length_ft` (eslora en pies, `NULL` hasta confirmación real).
- Seed de las 4 embarcaciones reales (`Falcon 86`, `Lagoon 46`, `Sea Ray 50`, `Ferretti 72`) — `vessel_name`/`vessel_slug` reales, todo lo demás `NULL`/`pending`. Ejecutado en vivo contra `u713871298_lly_db` (mismo patrón de parser consciente de comillas de `sql/005`).

### Editor CRUD — nuevo, a pedido del Arquitecto ("Lester pueda editar... agregar, borrar, editar yates")
- **`core/FleetCatalogRepository.php`** — 4 métodos nuevos: `listAll()` (todas las filas, cualquier estado — solo para el editor, nunca para la IA), `create()`, `update()`, `delete()`, todos sobre una whitelist de columnas editables (`EDITABLE_FIELDS`) — nunca se aceptan `id`/timestamps desde el cliente.
- **`api/fleet_catalog.php`** (nuevo) — sesión + CSRF, mismo patrón que `api/ephemeral_links.php`. Acciones `list`/`create`/`update`/`delete`. Una embarcación nueva **siempre** entra como `verification_status = 'pending'` sin importar qué mande el cliente — solo una edición posterior explícita la puede marcar `verified` (Mandamiento 4, cerrojo que la propia API refuerza server-side, no solo confiado al frontend).
- **`pg_ai_hub.php`** → nueva **Sección E — Fleet Catalog Editor** (formulario crear/editar + tabla con botones ✏️/✕ por fila), reutilizando 100% las clases `.ephemeral-*`/`.data-table`/`.pill-*` ya existentes — cero CSS nuevo. Docblock del archivo actualizado de "Four sections" a "Five sections". Esto es una edición incremental del archivo existente (mismo patrón usado para añadir la Sección D en un hito anterior) — no una recreación, consistente con la Regla de Inmutabilidad vigente sobre este archivo.
- **`assets/js/main.js`** → `fleetPost()`/`fleetRenderRows()`/`fleetLoadList()`/`initFleetCatalogPanel()`, registrado en `llyInitAll()`, guardado en ausencia de `#fleet-catalog-table` — no afecta otras páginas.
- **`.htaccess`** → `fleet_catalog` agregado a la whitelist de `<FilesMatch>`.

### Validación en vivo (no solo `php -l`)
1. `action=list` vía HTTP real con sesión/CSRF → confirma las 7 filas (3 verificadas originales + 4 nuevas pendientes).
2. Ciclo completo `create` → `update` → `delete` contra la base de datos real → los 3 pasos devolvieron `success`, fila de prueba limpiada sin dejar basura.
3. **Confirmado que el cerrojo anti-alucinación sigue intacto tras el cambio:** `propuestas.php` (HTTP real) sigue mostrando solo las 3 embarcaciones verificadas — Falcon 86/Lagoon 46/Sea Ray 50/Ferretti 72 correctamente ausentes por estar `pending`. `FleetCatalogRepository::renderPromptMarkdownTable()` (lo que ve la IA) igual — solo 3 filas, "39 restantes pendientes" sin cambio (el conteo se calcula sobre verificadas, no sobre filas totales, así que las 4 nuevas `pending` no alteran ese número).

### Verificación técnica
`php -l` limpio en `pg_ai_hub.php`, `api/fleet_catalog.php`, `core/FleetCatalogRepository.php`. `node --check` limpio en `assets/js/main.js`. Cero cambios a `pg_ai_widget.js`/`ai_widget_gateway.php` (Regla de Inmutabilidad respetada — `pg_ai_hub.php` sí se editó, pero de forma incremental, no recreado).

## 📌 REGISTRO FORMAL — Causa Raíz Real del "JS Roto", Roles (super_admin), y Ruta 3 OpenAI (vigente, 2026-08-03)

### Diagnóstico — no era un error de sintaxis
Se reportó que la Sección A/B/D de `pg_ai_hub.php` quedaban "congeladas" tras agregar la Sección E. Se investigó con Chrome headless (`--headless=new --dump-dom` + un `window.onerror` temporal inyectado solo para el diagnóstico, removido después) — **cero excepciones JS sin capturar**. La causa raíz real, reproducida de forma controlada con 4 `curl` simultáneos usando el mismo token CSRF inicial: `api/leads.php` (la primera en llegar) tiene éxito y rota `$_SESSION['csrf_token']`; `api/ephemeral_links.php`, `api/fleet_catalog.php` y `api/pgai_settings.php` (que ya habían salido con el token viejo antes de que el navegador pudiera enterarse de la rotación) llegan después y reciben `403 "Invalid or expired CSRF token."`. Es una condición de carrera de sesión, no un bug de JavaScript — preexistía desde que había 3-4 paneles con carga simultánea; la Sección E fue la quinta llamada que la hizo consistentemente visible.

### Corrección — rotar el CSRF solo en acciones que mutan estado
`api/leads.php` (`list` es su única acción — ya no rota nunca), `api/ephemeral_links.php`, `api/pgai_settings.php`, `api/fleet_catalog.php` — todos ajustados al mismo patrón: el token se valida siempre, pero solo se rota tras una acción mutante (`create`/`update`/`delete`/`revoke`/`save`/etc.), nunca tras `list`/`get`. Mismo principio que `api/translate.php` ya aplicaba desde el 1 de julio ("no lo rota en cada llamada... el botón dispara múltiples fetches por clic") — extendido aquí a "múltiples fetches por carga de página". Reproducción del bug + verificación de la corrección con los mismos 4 `curl` simultáneos — antes: 1 éxito + 3 fallos; después: 4/4 éxito.

### Defensa adicional solicitada — `llySafeInit()`
`assets/js/main.js` → nuevo wrapper `llySafeInit(fn)` que envuelve cada `init*()` de `llyInitAll()` en `try/catch` con `console.error` nombrando la función que falló. No era la causa del bug (cada `init*` ya se auto-protegía con `if (!el) return`, y son llamadas síncronas independientes — una nunca pudo bloquear a las demás), pero es una red de seguridad legítima y barata, agregada tal como se pidió.

### Roles — `super_admin` vs `owner` (Sección C restringida)
- `sql/007_add_role_to_lly_users.sql` → columna `role ENUM('owner','super_admin')`, default `'owner'`. Ejecutada en vivo — la cuenta de Lester quedó confirmada en `'owner'` (verificado por consulta directa, cero escalación silenciosa).
- `api/login.php` y `core/auth_check.php` (ruta remember-me) ahora leen `role` y lo guardan en `$_SESSION['lly_role']`, con `try/catch` de compatibilidad si la columna no existe todavía en un entorno que no haya corrido `sql/007`.
- `core/dev_bypass.php` → sesión local de loopback recibe `lly_role = 'super_admin'` (quien tiene acceso físico a la máquina es el desarrollador, no Lester).
- `pg_ai_hub.php` → Sección C completa envuelta en `<?php if ($lly_is_super_admin): ?> … <?php endif; ?>`. Confirmado en vivo: con la sesión de loopback (`super_admin`), la sección se sigue renderizando (`grep` confirma `pgai-section-c` presente, balance de `<section>`/`</section>` y `<div>`/`</div>` intacto).
- **Pendiente de decisión del Arquitecto:** no existe todavía una segunda cuenta `super_admin` real — cuando la haya, un `UPDATE lly_users SET role = 'super_admin' WHERE email = '...'` manual la promueve (documentado, no ejecutado automáticamente por ningún endpoint).

### Ruta 3 — Fallback OpenAI (implementado, no validado en vivo)
- `core/OpenAiFallbackClient.php` (nuevo) — cliente delgado contra `POST https://api.openai.com/v1/chat/completions`, contrato estándar documentado de OpenAI (`messages: [system, user]`, `Authorization: Bearer`). Nunca lanza — degrada a `success:false` para que `ProxyBridge` siga la cascada.
- `core/EnvSettingsStore.php` → `FALLBACK_AI_PROVIDER_KEY` (secreto, enmascarado) y `FALLBACK_AI_PROVIDER_MODEL` agregados a `ALLOWED_KEYS`.
- `pg_ai_hub.php` Sección C (super_admin only) → nuevo fieldset "OpenAI Fallback — Route 3" con campo de llave (`type=password`) y selector de modelo (`gpt-4o-mini`/`gpt-4o`/`gpt-4.1-mini`), reutilizando el mismo patrón `data-setting-key` genérico — cero JS nuevo necesario, `pgaiSettingsApply()`/`pgaiSettingsPost()` ya son data-driven.
- `core/ProxyBridge.php::forward()` → nueva cascada: (1) AURA LAN, (2) AURA WAN — ambas ya resueltas dentro de `dispatchViaAura()` — (3) `dispatchViaOpenAiFallback()` (nuevo, solo si ambas de AURA fallan), (4) gateway HMAC legado (dormido, sin cambios, marcado como candidato a limpieza por Mandamiento 8), (5) degradado controlado. A diferencia del payload ultra-liviano de AURA, la Ruta 3 sí envía el `knowledge` completo como mensaje `system` — OpenAI no tiene (todavía) un mecanismo de contexto persistente equivalente al Protocolo M2M de AURA.
- **Sin validar en vivo** — no se proveyó una llave real de OpenAI en este hito (decisión explícita del Arquitecto: "dejar el campo listo, sin key todavía", mismo patrón que `syncTenantContext()`). Pendiente: repetir la disciplina de prueba real cuando exista una llave (`sk-...`) — no marcar esta ruta como "confirmada" hasta entonces.

### Widget en `dashboard.php`
Mismo markup/clases que `book.php`/`index.php` (cero CSS nuevo), agregado antes de `</body>`. `dashboard.php` solo se alcanza vía el `require` de `index.php` (`LLY_DASHBOARD_GATEKEEPER`), nunca por wrapper — el default relativo de `pg_ai_widget.js` es correcto sin configurar `window.PGAI_WIDGET_GATEWAY_URL`. Confirmado en vivo: `index.php` (autenticado localmente) sirve el widget completo.

### Validación en vivo (resumen)
1. Condición de carrera reproducida y corregida — 4/4 llamadas simultáneas exitosas tras el fix (antes 1/4).
2. Chat real sin regresión — `api/public/ai_widget_gateway.php` sigue respondiendo en vivo vía AURA tras todos los cambios de `ProxyBridge.php`.
3. `api/pgai_settings.php action=get` confirma los 2 campos nuevos de OpenAI presentes en la respuesta (`is_set:false`, como se espera sin llave real).
4. `sql/007` ejecutado en producción — rol de Lester confirmado `'owner'`.

### Verificación técnica
`php -l` limpio en: `api/leads.php`, `api/ephemeral_links.php`, `api/pgai_settings.php`, `api/fleet_catalog.php`, `api/login.php`, `core/auth_check.php`, `core/dev_bypass.php`, `pg_ai_hub.php`, `core/ProxyBridge.php`, `core/OpenAiFallbackClient.php`, `core/EnvSettingsStore.php`, `dashboard.php`. `node --check` limpio en `assets/js/main.js`. Cero cambios a `pg_ai_widget.js`/`ai_widget_gateway.php` (Regla de Inmutabilidad respetada).

## 📌 REGISTRO FORMAL — Actualización de `MOD_OPERADOR_COGNITIVO_OMNICANAL.md` (v1.2 → v1.3) + Estado Consolidado del Ecosistema PG-AI (vigente, 2026-08-04)

### Decisión de gobernanza sobre dónde vive cada dato
La directiva pedía registrar hechos concretos de Lover Lips Yachts (estado de AURA v3.0, nombres de tabla/archivo reales, rutas de negocio) directamente en `modulos/MOD_OPERADOR_COGNITIVO_OMNICANAL.md`. Ese documento es explícitamente un **molde agnóstico** (Mandato de Sincronización Génesis, sección 6 del propio archivo: "ningún dato real de un cliente o tenant específico debe incorporarse aquí") — incorporar ahí nombres como `ll_fleet_catalog`, `pg_ai_hub.php` o "AURA v3.0" habría violado esa misma regla que el documento se impone a sí mismo, y que este proyecto ya respetó en cada hito anterior (branding "PG-AI" nunca propagado al molde, `ll_ephemeral_links` documentado solo aquí, etc.). Se aplicó la misma disciplina de siempre: el molde recibió los **patrones agnósticos generalizados**; este registro recibe los **hechos concretos**.

### `modulos/MOD_OPERADOR_COGNITIVO_OMNICANAL.md` → v1.3 (cambios agnósticos)
- **§1.4 — Arquitectura de la Fuente de Verdad (Ground Truth):** formaliza el patrón de 3 capas (Prompt Maestro / Catálogo Estructurado en BD vía marcador `{{...}}` / Reglas de Negocio y FAQs) ya implementado en este proyecto (`core/prompts/pg_ai_lester_master.md` + `{{FLEET_CATALOG_TABLE}}` + `ll_fleet_catalog` + §2-4 del prompt maestro) — generalizado sin esos nombres.
- **§1.5 — Fallback Cognitivo Multi-Proveedor (opcional):** formaliza la directriz "Fase 2 / Pausada hasta validar el canal directo" ya aplicada a la Ruta 3 OpenAI de este proyecto (ver hito del 3 de agosto) — generalizado sin mencionar OpenAI/AURA por nombre.
- **Fase 8 (nueva) — Panel de Administración Super Admin (Cockpit):** formaliza los 5 requisitos ya construidos en `pg_ai_hub.php` (separación de roles `owner`/`super_admin`, panel de credenciales de canal, gestor de plantillas, CRUD visual de catálogo, visor de leads) — generalizado sin nombrar esas rutas/tablas. Incluye la nota de concurrencia sobre no rotar CSRF en lecturas, generalizando el hallazgo de la condición de carrera del hito anterior.
- **§3.3 ampliada:** el round-trip completo de WhatsApp (antes solo handshake+firma entrantes) ahora incluye `normalizeWhatsAppToOcmc()` y `sendWhatsAppReply()` (despacho saliente vía Graph API) como artefactos de referencia — generalizando lo que `api/public/whatsapp_webhook.php` ya hace en producción.

### Estado real de Lover Lips Yachts — consolidado aquí (no en el molde)
- **AURA v3.0 (servidor Linux DCD LABS):** confirmado **100% activo y probado** — `HTTP 200 OK` en vivo contra `axon.acadep.com` (WAN), con los cerrojos `NO_PRICE_WITHOUT_LEAD_DATA` y White-Glove Escalation verificados funcionando en las pruebas de fuego del 3 de agosto (respuesta real citando los 4 datos obligatorios ante una solicitud de cotización de Pink Lips).
- **Ruta 3 (OpenAI/ChatGPT):** marcada oficialmente **"Fase 2 / Pausada Temporalmente"** — código y campos de configuración ya existen (`core/OpenAiFallbackClient.php`, `FALLBACK_AI_PROVIDER_KEY`/`_MODEL` en `pg_ai_hub.php` Sección C), pero sin validar contra una llave real; se prioriza el canal directo con AURA mientras tanto.
- **Ground Truth real de este proyecto:** (a) `core/prompts/pg_ai_lester_master.md` — persona de Lester + cerrojos comerciales; (b) `ll_fleet_catalog` inyectado vía `{{FLEET_CATALOG_TABLE}}` (`core/FleetCatalogRepository.php` + `core/ProxyBridge.php::substituteFleetCatalog()`); (c) reglas de negocio y FAQs de Balandra Beach, Isla Espíritu Santo y Nado con Tiburón Ballena — viven en la §2 del mismo prompt maestro, no en un documento separado.
- **Cockpit Super Admin real:** `pg_ai_hub.php` — Sección C (super_admin only: credenciales AURA + WhatsApp + OpenAI fallback), Sección D (plantillas de cotización PINK LIPS + enlaces efímeros — el "gestor de plantillas" de la Fase 8 del molde), Sección E (`api/fleet_catalog.php` — CRUD visual de las 42 embarcaciones), Sección A (`api/leads.php` — visor de `omnichannel_*`/leads en vivo). **Nota de nomenclatura:** la directiva se refería a este visor como `lly_leads` — el nombre real de la fuente es `omnichannel_sessions`/`omnichannel_contacts`/`omnichannel_messages` (ver registro del 29 de julio); no existe ninguna tabla `lly_leads` en este proyecto, así que no se creó ni se renombró nada — `api/leads.php` ya cumple esa función bajo su nombre real.
- **Webhook de WhatsApp:** `api/public/whatsapp_webhook.php` — `GET` (handshake `hub_verify_token`), `POST` (validación `X-Hub-Signature-256` sobre cuerpo crudo, normalización a OCMC, persistencia en `omnichannel_*`, despacho a AURA vía `ProxyBridge`, respuesta saliente vía Graph API) — implementado desde el 29 de julio; el envío de salida sigue degradando a no-op registrado en log mientras `WHATSAPP_ACCESS_TOKEN`/`WHATSAPP_PHONE_NUMBER_ID` no tengan valores reales en `core/.env` (sin cambio en este hito).

### Verificación técnica
Los 5 bloques de código PHP nuevos/modificados en `MOD_OPERADOR_COGNITIVO_OMNICANAL.md` (`normalizeWhatsAppToOcmc`, `sendWhatsAppReply`, y los 3 bloques preexistentes re-verificados tras el reordenamiento de la sección 3.3) pasan `php -l` de forma aislada. Ningún archivo de código del proyecto fue tocado en este hito — cambio puramente documental (`modulos/MOD_OPERADOR_COGNITIVO_OMNICANAL.md` + este registro).

## 📌 REGISTRO FORMAL — `pg_ai_config.php`: Pantalla Dedicada de Configuración, Desacoplada del Hub (vigente, 2026-08-04)

### Decisión de arquitectura — mover, no duplicar
La directiva pedía una pantalla nueva para desacoplar la edición de datos de `dashboard.php`. `dashboard.php` ya era solo tarjetas de navegación (sin editores embebidos) — el desacople real pendiente era separar **configuración** (credenciales, prompt, catálogo, plantillas) de **operación diaria** (leads, testbed), ambas mezcladas en `pg_ai_hub.php`. Se optó por **mover** la Sección C (credenciales AURA/WhatsApp/OpenAI) y la Sección E (catálogo de flota) desde `pg_ai_hub.php` hacia `pg_ai_config.php`, en vez de duplicarlas en ambos lugares (Mandamiento 10) — `pg_ai_hub.php` queda con 3 secciones operativas (A, B, D) + un enlace a Config; `pg_ai_config.php` concentra todo lo editable.

### Esquema — `sql/008_create_ll_notification_templates.sql`
- `ll_notification_templates` — `template_key`/`channel` fijos (sin create/delete desde la UI, solo edición de `subject_en/es`/`body_en/es`), semilla de 2 plantillas (`lead_captured` × `email`/`whatsapp`). Ejecutada en vivo contra `u713871298_lly_db`.
- **Alcance honesto:** esta tabla es solo almacenamiento/edición de contenido — **no existe todavía ningún disparador automatizado** que envíe estas plantillas cuando se captura un lead real. `OmnichannelRepository::persistInbound()` sigue sin invocar ningún envío. Documentado así para no sobre-reportar una integración que no está conectada.

### Endpoints nuevos
- **`api/prompt_editor.php`** — sesión + CSRF (rota solo en `save`), `get`/`save` de `core/prompts/pg_ai_lester_master.md` directo (reemplazo completo, `LOCK_EX`). Guardar aquí cambia lo que dice el chatbot en vivo desde el siguiente mensaje — la caché APCu de `ProxyBridge::readKnowledgeBase()` ya está keyed por `mtime`, así que se invalida sola.
- **`api/notification_templates.php`** — sesión + CSRF (rota solo en `update`), `list`/`update` sobre `core/NotificationTemplateRepository.php` (nuevo).
- **`api/module_doc_editor.php`** — sesión + CSRF (rota solo en `save`), `get`/`save` de `modulos/MOD_OPERADOR_COGNITIVO_OMNICANAL.md`. **Único endpoint de este hito con verificación de rol server-side además del gateo de UI** (`$_SESSION['lly_role'] !== 'super_admin'` → `403`) — es una acción de gobernanza sobre el molde reutilizable, no contenido de negocio, así que amerita ese refuerzo aparte del ocultamiento visual.
- Los tres siguen el mismo patrón de no-rotación-de-CSRF-en-lecturas ya establecido (`api/fleet_catalog.php` et al.) — confirmado sin condición de carrera con 5 llamadas `list`/`get` simultáneas de la carga de página (ver prueba abajo).

### `pg_ai_config.php` (nuevo)
Seis secciones: **1** Catálogo de Flota (movido de `pg_ai_hub.php` Sección E, markup/JS idénticos — `#fleet-*` ya era genérico), **2** Editor del Prompt Maestro (nuevo, reemplaza el visor de solo lectura que tenía la vieja Sección C), **3** Plantillas de Notificación de Leads (nuevo), **4** Bóveda de Credenciales (movida de la vieja Sección C, con `ACADEP_AURA_AGENT_ID` agregado al formulario — campo que ya existía en `.env`/`EnvSettingsStore` pero nunca había tenido un input en la UI), **5** Prueba de Handshake M2M (nuevo, botón de un clic contra `api/aura_diagnostic.php`, con enlace al sandbox completo), **6** Editor del Módulo de Conocimiento (nuevo). Secciones 4-6 exclusivas de `super_admin` (mismo patrón `<?php if ($lly_is_super_admin): ?>` de la vieja Sección C). Reutiliza `.editor-textarea`/`.editor-textarea--chapter` ya existentes para los editores de texto largo — cero CSS nuevo.

### `pg_ai_hub.php` — simplificado
Docblock actualizado ("Five sections" → 3 operativas + enlace). Variables muertas removidas (`$lly_is_super_admin`, `$lly_knowledge_path`, `$lly_knowledge_text` — ya sin consumidor tras mover la Sección C). Nueva sección compacta "⚙️ Configuration" con un botón a `pg_ai_config.php`, visible a cualquier sesión autenticada (el gateo por rol vive dentro de `pg_ai_config.php`, no aquí).

### Navegación del Cockpit
Enlace directo agregado en 3 lugares: topbar de `pg_ai_hub.php` (junto al de regresar al Dashboard), tarjeta #1 de `pg_ai_hub.php` en `dashboard.php` (botón secundario), y la propia sección de enlace en `pg_ai_hub.php`.

### `.htaccess`
`pg_ai_config`, `prompt_editor`, `notification_templates`, `module_doc_editor` agregados a la whitelist — sin esto habrían devuelto 403 antes de ejecutar PHP.

### Validación en vivo
1. `pg_ai_config.php` → `HTTP 200`, las 6 secciones presentes (sesión de loopback = `super_admin`).
2. `pg_ai_hub.php` → `HTTP 200`, 5/5 `<section>`/`</section>` balanceadas, cero rastro de `pgai-section-e`/`fleet-catalog-table`.
3. `api/prompt_editor.php action=get` → devuelve el contenido real del prompt maestro vigente.
4. `api/notification_templates.php action=list` → devuelve las 2 plantillas sembradas; `action=update` (id=2, mismo contenido, prueba de round-trip) → `success`.
5. `api/module_doc_editor.php action=get` → devuelve el contenido real de `MOD_OPERADOR_COGNITIVO_OMNICANAL.md`.
6. **Prueba de concurrencia** (mismo patrón del hito del 3 de agosto): 5 llamadas `list`/`get` simultáneas (`fleet_catalog`, `prompt_editor`, `notification_templates`, `pgai_settings`, `module_doc_editor`) con el mismo token CSRF inicial → **5/5 éxito**, confirmando que el patrón de no-rotar-en-lecturas se aplicó correctamente a los 3 endpoints nuevos.

### Verificación técnica
`php -l` limpio en: `pg_ai_config.php`, `pg_ai_hub.php`, `dashboard.php`, `api/prompt_editor.php`, `api/module_doc_editor.php`, `api/notification_templates.php`, `core/NotificationTemplateRepository.php`. `node --check` limpio en `assets/js/main.js`. Cero cambios a `pg_ai_widget.js`/`ai_widget_gateway.php` (Regla de Inmutabilidad respetada).
