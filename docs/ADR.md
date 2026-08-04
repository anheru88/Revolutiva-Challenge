# Architecture Decision Records (ADR)

Registro de decisiones arquitectónicas del componente **PayIn Platform**.
Formato basado en [Michael Nygard](https://cognitect.com/blog/2011/11/15/documenting-architecture-decisions).

---

## ADR-001 · Arquitectura Hexagonal (Ports & Adapters)

- **Estado:** Aceptada
- **Contexto:** Se requiere un componente reusable, testeable y con capacidad de soportar múltiples proveedores de pago sin acoplar el núcleo de negocio al framework.
- **Decisión:** Adoptar Arquitectura Hexagonal con las capas `Domain`, `Application`, `Infrastructure` y `Shared`. El dominio no dependerá de Laravel ni de infraestructura.
- **Consecuencias:**
  - (+) Dominio aislado y testeable con pruebas unitarias puras.
  - (+) Incorporar un proveedor nuevo no impacta el dominio.
  - (−) Mayor cantidad de clases y mapeos (DTO ↔ dominio ↔ Eloquent).

---

## ADR-002 · Dominio desacoplado de Laravel

- **Estado:** Aceptada
- **Contexto:** El dominio debe ser independiente del framework para maximizar reutilización y testeo.
- **Decisión:** Las entidades y Value Objects (`Money`, `Email`, `UUID`) se implementan en PHP puro. La persistencia con Eloquent vive únicamente en `Infrastructure`, mapeando desde/hacia el dominio vía repositorios.
- **Consecuencias:**
  - (+) Tests de dominio sin arranque de Laravel.
  - (−) Se requiere una capa de mapeo explícita entre modelos Eloquent y entidades de dominio.

---

## ADR-003 · Selección de proveedor mediante ProviderResolver

- **Estado:** Aceptada
- **Contexto:** El proveedor de pago se indica en el request y debe resolverse a un adaptador concreto.
- **Decisión:** Introducir un `ProviderResolver` (Factory + Strategy) que devuelve el `PaymentProviderAdapter` correspondiente según el código de proveedor.
- **Consecuencias:**
  - (+) Registrar un proveedor nuevo = registrar un adaptador; sin cambios en el caso de uso.
  - (+) Cada adaptador implementa un puerto común (`PaymentProviderPort`).

---

## ADR-004 · Procesamiento síncrono con diseño listo para asíncrono

- **Estado:** Aceptada
- **Contexto:** El alcance actual es procesamiento síncrono, pero se anticipa migrar a asíncrono.
- **Decisión:** El caso de uso `ProcessPayIn` se ejecuta síncronamente detrás de un puerto de aplicación. La lógica queda encapsulada para poder envolverse en un Job (cola) sin tocar el dominio.
- **Consecuencias:**
  - (+) Migración a Jobs sin reescritura del núcleo.
  - (−) En síncrono, la latencia del proveedor impacta el request.

---

## ADR-005 · Identificadores duales (ID interno + UUID público)

- **Estado:** Aceptada
- **Contexto:** Se necesitan relaciones eficientes y un identificador público estable.
- **Decisión:** Usar `id` entero autoincremental para relaciones internas y `uuid` como identificador público expuesto en la API.
- **Consecuencias:**
  - (+) Integridad relacional eficiente + no exposición de secuencias internas.
  - (−) Doble columna e índice único sobre `uuid`.

---

## ADR-006 · Auditoría de proveedor e historial de estados

- **Estado:** Aceptada
- **Contexto:** Trazabilidad de lo enviado/recibido del proveedor y de las transiciones de estado.
- **Decisión:** Persistir `provider_request` / `provider_response` en `pay_ins` y registrar cada transición en `pay_in_status_history`.
- **Consecuencias:**
  - (+) Auditoría completa del ciclo de vida.
  - (−) Crecimiento de almacenamiento por payloads y por historial.

---

## ADR-007 · Máquina de estados de PayIn

- **Estado:** Aceptada
- **Contexto:** El PayIn transita por estados bien definidos.
- **Decisión:** Estados `CREATED → VALIDATED → PROCESSED` con `FAILED` como estado terminal de error. Las transiciones se validan en el dominio.
- **Consecuencias:**
  - (+) Transiciones inválidas rechazadas en el núcleo.
  - (−) Requiere mantener la tabla de transiciones válidas.

---

## ADR-008 · API REST versionada y snake_case

- **Estado:** Aceptada
- **Contexto:** Estabilidad y consistencia del contrato público.
- **Decisión:** Prefijo `/api/v1`. Todos los campos de request/response en `snake_case`.
- **Consecuencias:**
  - (+) Evolución sin romper clientes existentes.
  - (−) Mapeo entre convención de dominio y contrato de API.

---

## ADR-009 · Operaciones transaccionales

- **Estado:** Aceptada
- **Contexto:** Persistir PayIn, actualizar estado y registrar historial deben ser atómicos.
- **Decisión:** Envolver las escrituras del caso de uso en una transacción de base de datos.
- **Consecuencias:**
  - (+) Sin estados inconsistentes ante fallos parciales.
  - (−) Cuidado con llamadas externas dentro de la transacción (proveedor).
