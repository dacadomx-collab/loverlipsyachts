# 🧬 00 - ADN DEL PROYECTO (DIRECTRIZ MAESTRA)

## 📌 1. IDENTIDAD DEL PROYECTO
- **Nombre del Proyecto:** Lover Lips Yachts — Owner Dashboard + Concierge IA
- **Cliente / Dueño:** Lester Keizer (y Fabiola Keizer), Lover Lips Yachts — charter de yates de lujo en La Paz, BCS.
- **Objetivo Principal:** Panel de administración privado (Owner Dashboard) para la operación del negocio de charters, más un operador cognitivo omnicanal ("Concierge IA Lover Lips") que atiende leads por Web/WhatsApp, captura datos de forma determinística, y un CRM de leads + calendario de disponibilidad híbrido. Incluye además páginas públicas satélite (book spotlight, invitación VIP a eventos) para el libro *Nine Lives. One True Love* del propietario.

## 🛠️ 2. STACK TECNOLÓGICO Y ARQUITECTURA
- **Frontend:** HTML/CSS/JS nativo (sin framework, sin build step). Bilingüe nativo vía atributos `data-lang="en"/"es"` con toggle CSS (`assets/js/main.js`). Toggle de tema Light/Dark vía `[data-theme]` en `<html>`, anti-flash con `assets/js/theme-init.js` cargado bloqueante en `<head>`.
- **Backend:** PHP 8.x (`declare(strict_types=1)`), sin framework. Conexión centralizada vía `api/conexion.php` (PDO, detección local-vs-producción por `HTTP_HOST`/`SERVER_ADDR`/`REMOTE_ADDR`).
- **Base de Datos:** MariaDB/MySQL — **una sola base de datos compartida** (`u713871298_lly_db` en Hostinger); no existe una copia local separada, incluso en XAMPP local se conecta a la misma BD remota vía `DB_HOST_LOCAL`.
- **Arquitectura híbrida real (importante — no es un stack único):** el sitio de marketing público principal (`loverlipsyachts.com`) es **WordPress**; este repositorio es el **"cockpit"** — un portal de administración y operador de IA en PHP nativo, desplegado bajo `/cockpit/` en el mismo dominio, más una carpeta hermana `/my-book/` para páginas públicas sin muro de login (book spotlight, invitación de eventos).
- **Infraestructura / Despliegue (CI/CD FTP):** GitHub Actions (`.github/workflows/deploy.yml`), dos jobs FTPS independientes vía `SamKirkland/FTP-Deploy-Action`:
  - Job 1: raíz del repo → `public_html/cockpit/` (excluye `.md`, `.sql`, `docs/**`, `knowledge/**`, `core/.env`, media sin optimizar, etc. — ver exclusion matrix completa en el propio `deploy.yml`).
  - Job 2: `./my-book/` → `public_html/my-book/`.
  - **Servidor FTP:** `files.loverlipsyachts.com` (Hostinger). **Usuario:** `u713871298`. Password y demás secretos viven **solo** en GitHub Secrets (`FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`) — nunca en el repo.
  - `core/.env` está explícitamente excluido del deploy — las credenciales de producción se configuran a mano en el servidor (Hostinger File Manager/SSH), nunca viajan por git.

## 🧩 3. MÓDULOS PRINCIPALES (CORE FEATURES)
1. **Concierge IA Lover Lips (Operador Cognitivo Omnicanal):** chatbot IA multicanal (Web widget + WhatsApp), memoria conversacional cross-turno, extracción determinística de leads (regex + plantilla, nunca delegada al LLM), fallback multi-proveedor (OpenAI directo / satélite AURA vía ACADEP). Ver `modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md` para el blueprint agnóstico completo.
2. **Leads CRM (`leads.php` + `api/leads.php`):** vista de una fila por sesión/cliente (nunca por mensaje), búsqueda + filtro de fecha, modal de detalle con resumen ejecutivo + transcripción completa.
3. **Agenda / Calendario Híbrido (`agenda.php` + `api/bookings.php`):** calendario de dos niveles — 🟡 interesados leídos en vivo de `omnichannel_sessions` (nunca duplicados), 🟢 reservas formales confirmadas en `yacht_bookings`, exclusión automática por consulta cuando un lead se convierte.
4. **Enlaces Efímeros / Cotizaciones (`api/public/l.php`, `core/EphemeralLinkManager.php`):** enlaces de cotización autodestructivos por conteo de vistas, más modo de vista previa estática (`?sample=slug`) para material de demostración sin expiración.
5. **Catálogo de Flota (`core/FleetCatalogRepository.php`, `api/fleet_catalog.php`):** fuente única de verdad para los yates que consume tanto el prompt maestro de la IA como el editor CRUD del cockpit.
6. **Book Spotlight + Invitación VIP (`book.php`/`my-book/index.php`, `invitation.php`/`my-book/invitation.php`):** páginas públicas satélite para el libro *Nine Lives. One True Love* de Lester Keizer y sus eventos de lanzamiento.
7. **Owner Dashboard / Reportes / Alianzas / Propuestas:** panel privado con autenticación por sesión (`core/auth_check.php`), historial de reportes técnicos pagados bajo el modelo de sociedad tecnológica (ver regla de negocio abajo), y propuestas de fases futuras.

## 🔌 4. INTEGRACIONES Y TERCEROS (APIs)
- **Pasarela de Pago:** N/A — no hay checkout de e-commerce en este sistema; los pagos de reportes/alianzas se registran manualmente, no se procesan aquí.
- **Otras APIs / Servicios:**
  - **OpenAI** (`core/OpenAiFallbackClient.php`) — ruta de despacho directa, primaria cuando hay llave configurada.
  - **AURA / ACADEP** (`core/AuraSatelliteClient.php`) — canal satélite M2M vía `axon.acadep.com`, fallback/alterna según `PRIMARY_AI_PROVIDER`.
  - **WhatsApp Business** — número publicado `+1 702 204 8894` (`wa.me`), usado tanto por el widget de chat como por CTAs de RSVP/cotización.
  - **Google Translate** (`api/translate.php`) — auto-traducción de campos faltantes en el Book Editor Studio (migrado desde DeepL).

## ⚠️ 5. REGLAS ESPECÍFICAS DEL PROYECTO
- **Modelo de sociedad tecnológica 50% Cash / 50% Trade Credits:** los reportes/desarrollos ya ejecutados y pagados en `alianzas.php` se dividen bajo este esquema — regla de negocio verificada en producción (`alianzas.php`), no una suposición de este documento.
- **Mobile-First estricto + Modo Oscuro nativo:** todo componente nuevo nace sin anchos fijos en `px` en contenedores principales, y con soporte de tema Light/Dark vía los tokens `--bg`/`--surface`/`--ink` de `assets/css/style.css`.
- **Bilingüe nativo obligatorio:** todo texto nuevo visible al usuario nace con su par `data-lang="en"`/`data-lang="es"` — no hay excepciones documentadas salvo nombres propios/títulos de marca que no se traducen (ej. el título del libro).
- **Disciplina de migraciones SQL:** todo `sql/*.sql` se escribe pero se ejecuta manualmente vía phpMyAdmin — la IA Ejecutora nunca corre `CREATE TABLE`/migraciones directamente contra la base de datos de producción sin autorización humana explícita (Mandamiento 9, ver `docs/01_LEY_Y_MANDAMIENTOS.md`).
- **Autenticación real = sesión de servidor, no JWT:** `core/auth_check.php` usa sesiones PHP nativas (`session_start()`, `session_regenerate_id()`) + tokens CSRF (`hash_equals()`) — el Mandamiento 14 acepta explícitamente "JWT o Tokens de sesión"; este proyecto usa la segunda opción en todos sus endpoints reales. Ver la nota de corrección en `docs/03_API_CONTRACTS_AND_ROUTING.md`.