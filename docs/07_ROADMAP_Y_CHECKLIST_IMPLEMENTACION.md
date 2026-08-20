# 🧬 07 — ROADMAP Y CHECKLIST DE IMPLEMENTACIÓN COGNITIVA

> **Clasificación:** Documento Operativo — adaptado de la Plantilla Universal VECTOR_CERO v2.0 al proyecto real Lover Lips Yachts (2026-08-20).
> **Estado real (2026-08-20):** este checklist describe el estado objetivo/roadmap. Ninguna de las casillas de abajo refleja código construido todavía — el estado real de cada pieza ya construida vive en `docs/02_SYSTEM_CODEX_REGISTRY.md` (hitos fechados y verificados), no aquí. Este documento es la intención futura, no el registro de lo hecho.

---

## 🎯 OBJETIVO GENERAL DEL ROADMAP

Este documento establece las fases lógicas de desarrollo, los criterios mínimos de aceptación técnicos y las reglas de piedra conceptuales para guiar la construcción del **Concierge IA Lover Lips v1.0** (el operador cognitivo omnicanal ya parcialmente en producción — ver `modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md`). Ningún hito de programación se considerará cerrado si no se satisfacen los requerimientos descritos en este checklist.

### Anclaje contra el estado real ya construido — evita duplicar o contradecir lo que ya existe
- **Consent Gate (P0 en la tabla de abajo):** ya existe como **propuesta explícitamente marcada "no validada"** en `modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md` sección 3.5 (v2.4) — este proyecto (`loverlipsyachts.com`) **no tiene implementado** ningún mecanismo de consent gate todavía. La Fase A de abajo describe el mismo trabajo pendiente; no se ha empezado.
- **Human Handoff (P0):** el marcador `[[PGAI_ESCALATE]]` que resolvería esto **ya existe en el código** (`core/PgAiActionProcessor.php`) pero una auditoría en vivo (`docs/02_SYSTEM_CODEX_REGISTRY.md`, hito "Auditoría en Vivo Post-Sync") encontró que el modelo **no lo emite de forma confiable** — el mecanismo de post-procesamiento está construido, el disparo confiable a 0.75 de confianza descrito en la Fase C de abajo no.
- **Normalización Ontológica de Ingesta / Dataset de Flota (P1):** ya existe una tabla real, `ll_fleet_catalog` (`sql/005_create_ll_fleet_catalog.sql`, `core/FleetCatalogRepository.php`), con columnas `vessel_name`/`vessel_slug`/`role_label_en/es`/`max_pax`/`rate_note_en/es`/`status_pill`/`verification_status` — **no** `yacht_id`/`length_ft`/`amenities` como a veces se ha nombrado informalmente; cualquier expansión debe partir de este esquema real o migrarlo explícitamente, nunca asumir columnas que no existen (Mandamiento 4). Estado real de datos: **3 de 42 embarcaciones** de la flota total tienen `verification_status = 'verified'` — el resto son huecos de captura pendientes, no un error.

Las demás filas del cuadro de priorización (Motor de Riesgo del Dominio, Scoring Multidimensional, Deduplicación Omnicanal, Twin Engine, Persona Adaptativa, Retención Invisible) **no tienen ningún trabajo real iniciado todavía** en este proyecto — son roadmap puro, sin anclaje que corregir.

---

## 📊 CUADRO DE PRIORIZACIÓN DE ARQUITECTURA

| Prioridad | Componente / Requerimiento Conceptual | Descripción Técnica e Impacto |
| :--- | :--- | :--- |
| 🔴 **P0 - Crítico** | **Consent Gate — Flujo Conversacional** | Bloqueo obligatorio en los canales de mensajería para requerir aceptación explícita de términos antes de almacenar datos del usuario en el CRM. El backend debe rechazar cargas sin este token de confirmación. |
| 🔴 **P0 - Crítico** | **Motor de Riesgo del Dominio & Filtro Anti-Alucinación** | Estructura RAG en capas cerradas alimentada por documentos validados del equipo. Desvío inmediato a humano ante disparadores críticos definidos por el negocio (`[disparador_1]`, `[disparador_2]`). |
| 🔴 **P0 - Crítico** | **Human Handoff Fluido** | Protocolo de escalamiento que transfiere el historial conversacional completo con perfiles detectados al equipo cuando la confianza cae por debajo de 0.75. |
| 🟡 **P1 - Alto** | **Scoring Multidimensional de Perfiles** | Abandono de clasificaciones rígidas. Implementación de una huella de comportamiento dinámica mediante vectores flotantes (`[dimensión_1]`, `[dimensión_2]`, `[dimensión_3]`, `[dimensión_4]`). |
| 🟡 **P1 - Alto** | **Normalización Ontológica de Ingesta** | Mapeo previo de datos cargados desde fuentes legacy para estandarizar abreviaturas o errores hacia un diccionario centralizado antes del procesamiento. |
| 🟡 **P1 - Alto** | **Deduplicación Omnicanal de Identidad** | Algoritmo fuzzy-match para enlazar interacciones de distintos canales bajo una única ficha de usuario utilizando identificadores normalizados. |
| 🟢 **P2 - Magia** | **Motor de Gemelo Digital / Twin Engine** | Simulador matemático predictivo que cruza `[métricas_de_comportamiento]` y `[señales_de_estado]` para proyectar riesgo o tendencia a semanas vista. |
| 🟢 **P2 - Magia** | **Persona Conversacional Adaptativa** | Módulo que altera la sintaxis y los disparadores de enganche según el estilo cognitivo detectado (`[estilo_1]`, `[estilo_2]`, `[estilo_3]`). |
| 🟢 **P2 - Magia** | **Inteligencia de Retención Invisible** | Análisis longitudinal de señales de comportamiento (latencia en respuestas, cancelaciones, reducción de actividad) para predecir el abandono antes de que sea verbalizado. |

---

## 🛠️ CHECKLIST TÉCNICO DE IMPLEMENTACIÓN

### 🛫 Fase A: Capa de Entrada y Seguridad Legal
- [ ] **Despliegue del Webhook Central:** Configurar el punto de entrada lógico capaz de recibir y discriminar los payloads provenientes de los canales del proyecto.
- [ ] **Middleware de Consent Gate:** Interceptar el flujo entrante; si el usuario no cuenta con la bandera de autorización legal afirmativa persistida, el sistema congelará la IA libre y solo desplegará el flujo estructurado de aceptación de términos.
- [ ] **Motor de Interceptación de Riesgo del Dominio:** Programar un módulo de escaneo rápido de texto basado en diccionarios de peligro definidos por el negocio. Ante un positivo, detener la inferencia de lenguaje y activar el protocolo de seguridad con alerta prioritaria al equipo.

### 🏗️ Fase B: Ingesta Semántica y Estructuración de CRM
- [ ] **Procesador de Historiales Legacy:** Desarrollar el motor de lectura de archivos masivos para procesar e indexar estructuras históricas de usuarios provenientes de fuentes heredadas.
- [ ] **Capa de Normalización Ontológica:** Implementar la rutina de traducción que mapee términos inconsistentes hacia el vocabulario unificado controlado antes de cruzar los datos.
- [ ] **Estructuración de la Ficha Multidimensional:** Habilitar el almacenamiento dinámico basado en vectores de afinidad para guardar las "huellas comportamentales" del usuario sin encasillarlo rígidamente.

### 🛡️ Fase C: Arquitectura Anti-Alucinación y RAG
- [ ] **Segregación Estricta de Memoria Contextual:** Diseñar la separación lógica de contextos en el backend para impedir que datos transaccionales o de marketing contaminen los procesos de inferencia del dominio principal.
- [ ] **Filtro de Confidence Threshold:** Implementar el validador de umbrales numéricos de confianza fijado en 0.75 para cualquier cálculo de proyecciones o análisis del dominio.
- [ ] **Bitácora Inmutable de Auditoría:** Crear el repositorio físico de logs para registrar de manera permanente cualquier conversación donde el sistema haya detectado terminología de riesgo o escalación.

### 🚀 Fase D: Módulos Avanzados Predictivos ("Magia")
- [ ] **Simulador Asíncrono del Motor Predictivo:** Programar la lógica matemática del simulador numérico para proyecciones de estado futuro, aislándolo del hilo de ejecución web principal para mantener la latencia del servidor baja.
- [ ] **Inyector de Persona Conversacional Adaptativa:** Crear la capa lógica que modifique dinámicamente las variables gramaticales y de tono de las plantillas de mensajería saliente según el perfil dominante detectado.
- [ ] **Telemetría de Alerta Temprana (Retención):** Desarrollar las métricas de monitoreo longitudinal sobre comportamientos del cliente para disparar notificaciones humanas de reenganche estratégico.

---

## 📐 DIRECTRICES DE INFRAESTRUCTURA (REGLAS DE PIEDRA DEL ARQUITECTO)

1. **Desacoplamiento Absoluto de Prompts:** Queda estrictamente prohibido codificar de forma fija (hardcode) los prompts del sistema dentro de los scripts de backend. Todos los prompts maestros de extracción NLP, clasificación de intenciones y configuraciones de guardrails deben ser consumidos dinámicamente desde archivos de configuración dedicados o desde almacenamiento seguro.

2. **Manejo de Operaciones Pesadas en Segundo Plano:** El procesamiento analítico que involucre comparaciones masivas de datos o proyecciones a futuro jamás debe interrumpir el flujo transaccional de los webhooks de mensajería. Estos procesos se ejecutarán fuera del hilo principal mediante llamadas asíncronas o tareas programadas.

3. **Principio de Inferencia Probabilística:** Toda comunicación generada automáticamente por el motor cognitivo ante consultas de carácter sensible del dominio se redactará mandatoriamente utilizando lenguaje de probabilidad, sugerencia calificada y humildad epistemológica — anulando juicios definitivos o deterministas.

---

*Documento de plantilla operativa universal. Reemplazar todos los `[PLACEHOLDER]` con los valores y contexto específicos del proyecto antes de iniciar el desarrollo.*
