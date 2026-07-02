# 🗄️ 02 — DATABASE SCHEMA BLUEPRINT

> **Fuente de verdad única** para toda la estructura de datos del proyecto.
> La IA Ejecutora debe registrar AQUÍ cualquier tabla, columna o tipo de dato que cree o modifique.

---

## 📌 NOTA DE FUNDACIÓN

Todas las conexiones a esta base de datos deben realizarse **obligatoriamente** a través de la clase centralizada en `api/conexion.php`, leyendo las variables `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS` del archivo `.env`.

**PROHIBIDO** instanciar PDO directamente en un endpoint. **PROHIBIDO** hardcodear credenciales.

---

## 📊 MAPEO DE VARIABLES VALIDADAS (FRONT vs BACK)

| Concepto | DB / Backend (`snake_case`) | Frontend (`camelCase`) | Tipo de Dato |
| :--- | :--- | :--- | :--- |
| [CONCEPTO_1] | `variable_back` | `variableFront` | [String / Int / Bool / Timestamp] |
| [CONCEPTO_2] | `variable_back` | `variableFront` | [String / Int / Bool / Timestamp] |

> **Regla de Hierro (Mandamiento 7):** `snake_case` para todo lo que vive en la DB o el backend PHP. `camelCase` para todo lo que vive en el frontend JS/React. Cero excepciones.

---

## 🗄️ ESTRUCTURA DE TABLAS (SCHEMA)

### Convenciones Globales de Diseño
- Toda tabla tiene `id` (INT, AUTO_INCREMENT, PK), `created_at` (TIMESTAMP DEFAULT NOW()), `updated_at` (TIMESTAMP DEFAULT NOW() ON UPDATE NOW()).
- Charset: `utf8mb4`. Collation: `utf8mb4_unicode_ci`.
- Motor: `InnoDB` (soporte de transacciones y FK).
- Soft-delete preferido: columna `deleted_at` (TIMESTAMP NULL) en lugar de `DELETE` físico.

> **Nota (2026-07-01):** las dos tablas reales del sistema (`lly_users`, `lly_book_content`) predatan esta convención estricta y no siguen el patrón `created_at`/`deleted_at` al pie de la letra — se documentan tal cual existen, no como debieran ser. Ningún esquema se altera sin autorización humana explícita (Mandamiento 9).

---

### Tabla: `lly_users`
**Propósito:** Cuentas de acceso al Owner Dashboard (login + remember-me).

| Columna | Tipo | Nulo | Default | Descripción |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `email` | VARCHAR(255) | NO | — | Identificador de login |
| `password_hash` | VARCHAR(255) | NO | — | Hash `password_hash()` de PHP, nunca texto plano |
| `remember_token` | VARCHAR(255) | SÍ | NULL | Token de sesión persistente, rotado en cada uso |
| `token_expiry` | TIMESTAMP | SÍ | NULL | Ventana deslizante de 30 días |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |

**Índices:** `PRIMARY KEY (id)`.

---

### Tabla: `lly_book_content`
**Propósito:** Almacén EAV (Entity-Attribute-Value) de todo el contenido bilingüe del Book Spotlight (`book.php` público + `book_editor.php` privado). Un `meta_key` por campo lógico en vez de una columna fija por campo — permite agregar campos nuevos (ej. cards futuras, artículos de blog) sin `ALTER TABLE`.

| Columna | Tipo | Nulo | Default | Descripción |
| :--- | :--- | :--- | :--- | :--- |
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | PK |
| `meta_key` | VARCHAR(255) | NO | — | Identificador semántico único del campo — catálogo completo en `02_SYSTEM_CODEX_REGISTRY.md` |
| `content_en` | TEXT | SÍ | NULL | Contenido en inglés |
| `content_es` | TEXT | SÍ | NULL | Contenido en español |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Auto-actualizado en cada UPSERT (`ON DUPLICATE KEY UPDATE`) |

**Índices:**
- `PRIMARY KEY (id)`
- `UNIQUE (meta_key)` — habilita el patrón `INSERT ... ON DUPLICATE KEY UPDATE` usado por `api/book_editor.php`.

**Foreign Keys:** ninguna — tabla plana, sin relaciones.

**Consumidores:** `book_editor.php` (lectura + preload del formulario), `api/book_editor.php` (UPSERT vía prepared statements), `book.php` (lectura pública, degrada a defaults embebidos si la BD no responde).

---

## 🧠 REGISTRO SEMÁNTICO (VOCABULARIO CONTROLADO)

Este registro define los nombres técnicos oficiales del proyecto. La IA Ejecutora es la **única autoridad** para asignar nombres; el Arquitecto propone en lenguaje natural. Catálogo completo y detallado de `meta_key` de `lly_book_content`: ver `02_SYSTEM_CODEX_REGISTRY.md` → "Registro Formal de Esquema".

| Concepto de Negocio | Nombre Técnico Oficial | Notas |
| :--- | :--- | :--- |
| Testimonio maestro (texto) | `testimonial_quote` | **No** `testimonial` a secas — ese `meta_key` quedó huérfano tras la corrección del 2026-07-01 |
| Firma del testimonio | `testimonial_author` | Nombre + cargo del autor del testimonio |
| Tarjeta de curiosidad N | `card_N` / `card_N_icon` / `card_N_img` | N = 1…7 |
| Endpoint de traducción | `api/translate.php` | Proveedor Google Translate `gtx`, sin API key |

- ✅ **Términos Permitidos:** `testimonial_quote`, `testimonial_author`
- ❌ **Términos Prohibidos:** `testimonial` (huérfano, sin consumidores desde 2026-07-01), `testimonial_text`, `author_name` (sinónimos no usados en el código real)

---

## 🧩 REGISTRO DE COMPONENTES FRONTEND

| Componente | Ruta | Tipo | Estado | Props Principales |
| :--- | :--- | :--- | :--- | :--- |
| `[NombreComponente]` | `[ruta/archivo.tsx]` | [UI / Logic / Page] | [Active / WIP / Deprecated] | `[prop1, prop2]` |

**Reglas de Interfaz Aplicadas:**
- `[Componente_1]`: [Breve descripción de la regla de negocio que cumple]
