# ZEDVILLE — Especificación Consolidada para Auditoría de Código
**Preparado por:** Daniel (Product Owner) — para uso de Claude Code
**Propósito:** Este documento resume las decisiones de producto/diseño acordadas para Zedville. Úsalo como checklist para comparar contra el código real entregado por IT (Laravel + HTML/CSS/JS plano, MySQL, hosting en Hostinger).

---

## 0. Contexto del proyecto

Zedville es una plataforma de educación financiera gamificada para estudiantes de secundaria en Gran Canaria. Los estudiantes son "ciudadanos" de una ciudad virtual simulada, ganan moneda virtual ("zeds"), gestionan cuentas bancarias y completan actividades de presupuesto.

**Stack confirmado por IT:**
- Backend: Laravel (PHP)
- DB: MySQL, sin ORM tipo Eloquent — se usa `DB::table()` directo
- Frontend: HTML/CSS/JS plano + Tailwind (no framework tipo React/Vue)
- Comunicación: `fetch` directo (sin Axios)
- Rutas: carpeta `routes/`, sin convención de API tipo `/api/v1/`
- Auth: OAuth — **pero el objeto de usuario solo expone `email` y `password`** (ver bloqueador #1 abajo)
- Calendario: FullCalendar.js, guarda solo título de evento + fecha, en tabla de base de datos

**⚠️ Bloqueadores conocidos aún sin resolver por IT:**
1. No está confirmado cómo obtener desde el frontend el `student_id`, `class_id` y `role` del usuario logueado (el objeto de usuario reportado solo tiene email/password — esto es insuficiente para casi todas las features de rol).
2. Sistema de puntos: IT dio respuestas contradictorias ("no existe" en un documento, "sí existe" en otro) sin nombrar la función/endpoint concreto.
3. No se ha dado un ejemplo real de una ruta + método de controlador Laravel ya funcionando en el proyecto.

**Instrucción para Claude Code:** al auditar, verifica explícitamente si el código resuelve estos 3 puntos. Si no lo hace, señálalo como gap crítico, no asumas que "debe estar en algún otro archivo".

---

## 1. Arquitectura de Roles y Permisos

Cuatro niveles de rol:

| Rol | Descripción |
|---|---|
| Super Admin | Daniel + Jasmin. Únicos que pueden fijar el salario base. |
| School Admin | Administración del centro escolar |
| Tutor / Alcalde (Mayor) | Vista dual: Panel de Gestión + Vista de Ciudadano, alternable con un solo toggle, sin logout |
| Student / Citizen | Estudiante, participante del juego |

**Reglas específicas a verificar en código:**
- [ ] Salario base: solo editable por Super Admin (verificar restricción a nivel de backend, no solo UI)
- [ ] Toggle Tutor: Management Panel ↔ Citizen View sin re-login (¿cómo se maneja la sesión/rol activo?)
- [ ] Petitions (peticiones ciudadanas): expiran a los 3 meses — ¿hay lógica de expiración automática o hay que calcularla en cada request?
- [ ] Tutores crean/gestionan referendums pero **no votan** en ellos — verificar que el backend bloquee el voto de un tutor, no solo que la UI lo oculte
- [ ] Petitions requieren aprobación de tutor antes de ser visibles a otros estudiantes
- [ ] Conteo de firmas visible, pero **nunca los nombres** de quienes firmaron — revisar el endpoint que devuelve datos de una petición: ¿filtra nombres en el backend o los envía y los oculta en frontend? (esto es importante: si el JSON incluye nombres y el frontend solo los esconde visualmente, es un fallo de privacidad)
- [ ] Autopilot: proceso de fin de mes que aplica el promedio de actividad de la clase a (a) tutores inactivos, (b) estudiantes con ausencia aprobada. Activado por defecto — verificar si existe un cron job / scheduled task en Laravel (`app/Console/Kernel.php` o `routes/console.php`)
- [ ] Tutores pueden ver la abreviatura del nombre real del estudiante junto al pseudónimo — verificar que este campo exista en la tabla de estudiantes y se devuelva solo a roles con permiso (no a otros estudiantes)

---

## 2. Dashboard

- [ ] Diseño "calmado", centrado en información — separado deliberadamente de la ciudad 3D
- [ ] Acceso a la ciudad vía banner "Enter Zedville" (no integrado en el mismo layout)
- [ ] Widgets del dashboard — verificar qué backend soporta cada uno:
  - [ ] Bandeja de mensajes (requiere tabla `messages` — ¿existe?)
  - [ ] Agregación mensual de Mood Meter (por estudiante y por clase — ¿hay endpoint de agregación o se calcula en frontend con datos crudos?)
  - [ ] Valor de meta de ahorro (savings goal) por estudiante
- [ ] Elementos redundantes deliberadamente eliminados (ej. contadores que el profesor ya conoce, barras de progreso sustituidas por mensajes de refuerzo) — si el código los reintrodujo, señalarlo

---

## 3. Monthly Bank Statement

- [ ] **No existe API todavía según IT** — verificar si el zip incluye algo parcial o si sigue totalmente pendiente
- [ ] Debe exponer totales de Needs / Wants / Savings por estudiante
- [ ] Terminología financiera (auto-debit, direct deposit, regla 50/30/20, etc.) debe aparecer visible en la UI — no simplificar/ocultar por diseño

---

## 4. Sistema de Badges (Engagement + FinHero)

- [ ] Puntos del FinHero/Finman badge se otorgan **solo en el primer intento correcto** — el estudiante puede reintentar pero no gana puntos adicionales en reintentos. Verificar esta lógica específicamente en el backend (fácil de implementar mal permitiendo puntos repetidos)
- [ ] Los mensajes de "praise" (felicitación) describen el aprendizaje logrado, **nunca repiten el nombre del badge** en el texto — revisar los strings/mensajes en el código
- [ ] Confirmar función/endpoint real que otorga puntos (bloqueador #2) — IT ya generó archivos de rutas para estos badges antes; verificar si esos archivos coinciden con lo que ahora dicen que existe

---

## 5. Mood Meter

- [ ] Verificar existencia de tabla/estructura para registrar estado de ánimo por estudiante
- [ ] Agregación mensual por clase (usada en el Dashboard, ver sección 2)

---

## 6. City Hall (Civic Chamber + Wellbeing Room)

- [ ] Civic Chamber: referendums y petitions (ver reglas en sección 1)
- [ ] Wellbeing Room: verificar si tiene su propia lógica de datos o es solo contenido estático
- [ ] Welcome speech bubble (avatar) en City Hall: comportamiento first-visit vía `localStorage`, descartable por sesión o permanentemente, avatar debe seguir siendo clickeable para reabrir el mensaje

---

## 7. Citizen Activation (Onboarding)

- [ ] Flujo de activación de nuevos ciudadanos (estudiantes) — verificar que capture los datos necesarios para asignar student_id/class_id (relacionado con el bloqueador #1: si el onboarding no captura o vincula esto correctamente, ninguna feature de rol funcionará)

---

## 8. Calendar / Assignments

- [ ] FullCalendar.js de terceros, ya usado en el admin — confirmar en el zip cómo se guarda cada evento (tabla DB con título + fecha, según IT)
- [ ] Verificar que los nuevos módulos reutilicen esta tabla/estructura en vez de crear una paralela

---

## 9. Convenciones de código a verificar

- [ ] Archivos nuevos backend ubicados según estructura estándar de Laravel (`routes/`, `app/Http/Controllers/`, etc.) — IT confirmó "como estructura de Laravel" sin más detalle, así que Code debe inferir/verificar la convención real usada en el resto del proyecto
- [ ] Frontend: HTML plano + Tailwind — sin componentes reutilizables tipo librería de diseño (IT confirmó que no existe ninguna)
- [ ] Sin linting/formatting config (ESLint, Prettier) — no hay que buscar archivos de config que impongan un estilo
- [ ] Marcadores de integración: el código generado previamente para IT usa comentarios `// TODO` o `// IT TEAM` — verificar si el código real de IT respeta o ignora esos marcadores
- [ ] Modo demo: los HTML de referencia se construyeron con `DEMO_MODE = true` para revisión visual antes de integración — confirmar si el código de IT reemplazó correctamente esas partes por lógica real conectada a backend, o si quedaron mezcladas

---

## 10. Qué reportar

Para cada módulo de arriba, Claude Code debería indicar uno de estos estados:
- ✅ **Implementado y coincide con la spec**
- ⚠️ **Implementado parcialmente / con diferencias** (detallar cuáles)
- ❌ **No implementado / no encontrado en el código**
- ❓ **No se puede verificar** (ej. depende de los bloqueadores 1-3 sin resolver)

Y al final, una lista priorizada de los gaps más críticos — especialmente todo lo relacionado con roles/permisos y el objeto de sesión de usuario, ya que casi todo el resto depende de eso.
