# Architecture Decision Records (ADR)

Registro de decisiones arquitectónicas del componente **PayIn Platform**.

- [ADR-001 · Arquitectura Hexagonal](#adr-001---arquitectura-hexagonal)
- [ADR-002 · Dominio desacoplado de Laravel](#adr-002---dominio-desacoplado-de-laravel)
- [ADR-003 · Selección de proveedor mediante ProviderResolver](#adr-003---selección-de-proveedor-mediante-providerresolver)
- [ADR-004 · Procesamiento síncrono con diseño listo para asíncrono](#adr-004---procesamiento-síncrono-con-diseño-listo-para-asíncrono)
- [ADR-005 · Identificadores duales (ID interno + UUID público)](#adr-005---identificadores-duales-id-interno--uuid-público)
- [ADR-006 · Auditoría de proveedor e historial de estados](#adr-006---auditoría-de-proveedor-e-historial-de-estados)
- [ADR-007 · Máquina de estados de PayIn](#adr-007---máquina-de-estados-de-payin)
- [ADR-008 · API REST versionada y snake_case](#adr-008---api-rest-versionada-y-snake_case)
- [ADR-009 · Operaciones transaccionales](#adr-009---operaciones-transaccionales)
- [ADR-010 · Factory del agregado PayIn](#adr-010---factory-del-agregado-payin)
- [ADR-011 · El módulo es un paquete Composer](#adr-011---el-módulo-es-un-paquete-composer)

---

# ADR-001 - Arquitectura Hexagonal

## Estado

Accepted

## Contexto

El componente PayIn será el núcleo del procesamiento de transacciones de la plataforma y deberá soportar la incorporación de nuevos proveedores de pago sin afectar la lógica del negocio. Además, el documento de la prueba solicita que el diseño siga principios de Arquitectura Hexagonal o Clean Architecture y que el componente sea reusable.

## Decisión

Se implementará una Arquitectura Hexagonal organizada en los módulos:

- Domain
- Application
- Infrastructure
- Shared

El dominio contendrá exclusivamente la lógica de negocio y no tendrá dependencias hacia Laravel, Eloquent, HTTP ni la base de datos.

Toda interacción con el exterior se realizará mediante Puertos (Interfaces) y Adaptadores.

## Justificación

El objetivo principal es separar completamente las reglas de negocio de la infraestructura para que el dominio permanezca estable aunque cambien aspectos tecnológicos.

Con esta arquitectura será posible:

- Cambiar la base de datos sin modificar el dominio.
- Sustituir Laravel por otro framework manteniendo la lógica de negocio.
- Incorporar nuevos proveedores de pago implementando únicamente nuevos adaptadores.
- Ejecutar pruebas unitarias sobre el dominio sin depender de infraestructura externa.
- Mantener una clara separación de responsabilidades entre cada capa.

Aunque esta arquitectura implica un mayor número de clases respecto a una implementación tradicional en MVC, proporciona una solución mucho más mantenible y alineada con sistemas empresariales donde la evolución del negocio es constante.

## Consecuencias

### Positivas

- Bajo acoplamiento.
- Alta cohesión.
- Fácil mantenimiento.
- Mayor capacidad de crecimiento.
- Excelente testabilidad.
- Reutilización del dominio.

### Negativas

- Mayor complejidad inicial.
- Incremento en la cantidad de clases.
- Requiere una comprensión adecuada de los principios de arquitectura.

---

# ADR-002 - Dominio desacoplado de Laravel

## Estado

Accepted

## Contexto

Para que el dominio sea reusable y verdaderamente independiente, no debe conocer detalles del framework. Eloquent, los contenedores de servicios y los helpers de Laravel introducen acoplamiento que dificulta el testeo y la portabilidad.

## Decisión

Las entidades y Value Objects (`Money`, `Email`, `UUID`) se implementarán en PHP puro, sin heredar de clases de Laravel ni de Eloquent. La persistencia con Eloquent vivirá únicamente en `Infrastructure`, y los repositorios se encargarán de mapear entre modelos Eloquent y entidades de dominio.

## Justificación

Aislar el dominio del framework permite ejecutar pruebas unitarias sin arrancar Laravel, acelera la suite de tests y mantiene la lógica de negocio estable frente a cambios tecnológicos.

## Consecuencias

### Positivas

- Tests de dominio rápidos y sin infraestructura.
- Dominio portable y reusable.
- Frontera explícita entre negocio e infraestructura.

### Negativas

- Se requiere una capa de mapeo explícita (Eloquent ↔ dominio).
- Más código de traducción entre capas.

---

# ADR-003 - Selección de proveedor mediante ProviderResolver

## Estado

Accepted

## Contexto

El proveedor de pago se indica en el request y debe resolverse en tiempo de ejecución a un adaptador concreto. El diseño debe permitir incorporar nuevos proveedores sin modificar los casos de uso.

## Decisión

Se introducirá un `ProviderResolver` (patrones Factory + Strategy) que, a partir del código de proveedor, devuelve el `PaymentProviderAdapter` correspondiente. Todos los adaptadores implementan un puerto común (`PaymentProviderPort`).

## Justificación

Centralizar la resolución del proveedor detrás de una interfaz común desacopla el caso de uso de las implementaciones concretas y convierte la incorporación de un proveedor en el simple registro de un nuevo adaptador.

## Consecuencias

### Positivas

- Agregar un proveedor no impacta el dominio ni los casos de uso.
- Contrato uniforme para todos los proveedores.
- Fácil de mockear en pruebas.

### Negativas

- Una indirección adicional entre el caso de uso y el proveedor.
- Requiere un registro/configuración de adaptadores disponibles.

---

# ADR-004 - Procesamiento síncrono con diseño listo para asíncrono

## Estado

Accepted

## Contexto

El alcance actual contempla procesamiento síncrono, pero se anticipa la necesidad de migrar a procesamiento asíncrono mediante colas.

## Decisión

El paso de proveedor vive en su propio caso de uso, `ProcessPayInHandler`, separado de `CreatePayInHandler`. Recibe un **UUID**, no un objeto en memoria: recarga el agregado desde el repositorio y no comparte estado con quien lo invoca, que es justo lo que exige un worker de cola.

Hoy `CreatePayInHandler` lo llama en línea (síncrono). `ProcessPayInJob` es el adaptador de cola del mismo caso de uso, y pasar a asíncrono es sustituir una línea del caso de uso:

```php
$this->processPayIn->handle($payIn->uuid());              // síncrono (actual)
ProcessPayInJob::dispatch($payIn->uuid()->value());       // asíncrono
```

Ni el dominio ni la capa de aplicación cambian; solo cambia quién ejecuta el caso de uso.

## Justificación

Mantener la lógica encapsulada tras un puerto permite cambiar la estrategia de ejecución (síncrona → asíncrona) sin reescribir el núcleo, respetando el principio de inversión de dependencias.

## Consecuencias

### Positivas

- Migración a Jobs sin reescritura del dominio.
- Menor complejidad inicial.

### Negativas

- En modo síncrono, la latencia del proveedor impacta el tiempo de respuesta del request.
- Al recargar el agregado por UUID se paga una lectura extra que el camino puramente en memoria no necesitaría; es el precio de que ambos modos compartan un único camino de código.

---

# ADR-005 - Identificadores duales (ID interno + UUID público)

## Estado

Accepted

## Contexto

Se necesitan relaciones eficientes entre tablas y, al mismo tiempo, un identificador público estable que no exponga la secuencia interna de registros.

## Decisión

Se usará un `id` entero autoincremental para las relaciones internas y un `uuid` como identificador público expuesto en la API.

## Justificación

El entero mantiene índices y claves foráneas eficientes; el UUID evita filtrar información sobre volumen o secuencia y ofrece un identificador estable de cara al exterior.

## Consecuencias

### Positivas

- Integridad relacional eficiente.
- No se expone información interna en la API.

### Negativas

- Doble columna e índice único adicional sobre `uuid`.

---

# ADR-006 - Auditoría de proveedor e historial de estados

## Estado

Accepted

## Contexto

Se requiere trazabilidad de lo enviado y recibido del proveedor, así como de todas las transiciones de estado de la transacción.

## Decisión

Se persistirán `provider_request` y `provider_response` en la tabla `pay_ins`, y cada transición de estado se registrará en la tabla `pay_in_status_history`.

## Justificación

Almacenar la conversación con el proveedor y el historial de estados permite auditar, depurar y reconstruir el ciclo de vida completo de cada transacción.

## Consecuencias

### Positivas

- Auditoría completa del ciclo de vida.
- Facilita el diagnóstico ante incidencias.

### Negativas

- Mayor consumo de almacenamiento por payloads e historial.

---

# ADR-007 - Máquina de estados de PayIn

## Estado

Accepted

## Contexto

Una transacción PayIn atraviesa estados bien definidos y no toda transición entre ellos es válida.

## Decisión

Se definen los estados `CREATED → VALIDATED → PROCESSED`, con `FAILED` como estado terminal de error. Las transiciones válidas se validan dentro del dominio.

## Justificación

Modelar la máquina de estados en el dominio garantiza que las transiciones inválidas se rechacen en el núcleo, con independencia del punto de entrada.

## Consecuencias

### Positivas

- Transiciones inválidas imposibles fuera de las reglas definidas.
- Estados consistentes y predecibles.

### Negativas

- Requiere mantener la tabla de transiciones válidas al evolucionar el flujo.

---

# ADR-008 - API REST versionada y snake_case

## Estado

Accepted

## Contexto

El contrato público de la API debe ser estable y consistente entre todos sus campos.

## Decisión

Todos los endpoints se expondrán bajo el prefijo `/api/v1`. Los campos de request y response usarán `snake_case`.

## Justificación

El versionado permite evolucionar la API sin romper clientes existentes, y una convención de nombres única evita ambigüedades en el contrato.

## Consecuencias

### Positivas

- Evolución del contrato sin romper integraciones.
- Contrato consistente y predecible.

### Negativas

- Mapeo entre la convención del dominio y la del contrato de API.

---

# ADR-009 - Operaciones transaccionales

## Estado

Accepted

## Contexto

Crear el PayIn, actualizar su estado y registrar el historial deben ocurrir de forma atómica para evitar estados inconsistentes. Además, el orquestador debe **persistir la transacción antes de enviarla al proveedor**.

## Decisión

El caso de uso realiza **dos escrituras atómicas**: (1) persiste el PayIn en estado `VALIDATED` (con su historial `CREATED`/`VALIDATED`) **antes** de invocar al proveedor, y (2) tras la respuesta del proveedor, actualiza el estado final (`PROCESSED`/`FAILED`) y su historial. La llamada al proveedor ocurre **entre** ambas escrituras, fuera de cualquier transacción.

## Justificación

Persistir antes de enviar al proveedor garantiza que siempre exista un registro auditable de la transacción, incluso si el proveedor falla o no responde. Envolver cada escritura en una transacción evita estados intermedios inconsistentes, y mantener la llamada externa fuera de la transacción evita bloquear conexiones de base de datos durante la latencia del proveedor.

## Consecuencias

### Positivas

- Sin estados inconsistentes ante fallos parciales.

### Negativas

- Debe evitarse mantener llamadas externas lentas (proveedor) dentro de la transacción de base de datos.

---

# ADR-010 - Factory del agregado PayIn

## Estado

Accepted

## Contexto

El caso de uso de creación resolvía las entidades por UUID y, acto seguido, ensamblaba el agregado a mano: generaba el UUID público y traducía cada entidad a su identificador interno. Mezclaba orquestación con construcción, y cualquier cambio en la forma del agregado obligaba a tocar el caso de uso.

## Decisión

`PayInFactory`, en el dominio, concentra el ensamblado del agregado a partir de las entidades ya resueltas. El caso de uso pide la pieza construida y sigue con la orquestación.

## Justificación

La factory conoce las reglas de construcción (UUID público nuevo, identificadores internos para las relaciones, estado inicial `CREATED`) y las mantiene en un único punto del dominio. Completa el catálogo de patrones del PRD §11 junto a Repository, Strategy, Adapter, DTO, Value Objects e inyección de dependencias.

## Consecuencias

### Positivas

- Construcción del agregado en un solo sitio, verificable con pruebas unitarias sin framework.
- El caso de uso queda más corto y centrado en orquestar.

### Negativas

- Una indirección más entre el caso de uso y el agregado.

---

# ADR-011 - El módulo es un paquete Composer

## Estado

Accepted

## Contexto

El dominio ya era independiente de Laravel (ADR-002), pero el módulo seguía repartido por el esqueleto del framework: las migraciones en `database/migrations`, las pruebas en `tests/`. Reutilizar el componente en otra aplicación significaba ir a recoger piezas por varios directorios.

## Decisión

`src/` es un paquete Composer propio, `revolutiva/payin`, que la aplicación instala mediante un repositorio de tipo `path` (symlink en `vendor/`). El paquete declara sus dependencias, registra sus ServiceProviders por *package discovery*, y guarda dentro sus **migraciones** (`src/<Modulo>/Infrastructure/Persistence/Migrations`, cargadas con `loadMigrationsFrom`) y sus **pruebas** (`src/<Modulo>/Tests`).

## Justificación

Un módulo auto-contenido se mueve o se extrae a su propio repositorio sin arrastrar la estructura de directorios de Laravel, y sus límites dejan de depender de una convención: los declara el `composer.json` del paquete.

## Consecuencias

### Positivas

- El componente es reusable de verdad: se copia o se publica el paquete y funciona.
- Las migraciones y las pruebas viajan con el código que describen.

### Negativas

- `composer install` necesita que `src/` exista antes de resolver dependencias (contemplado en el Dockerfile).
- Queda un único archivo fuera de `src/`: `tests/Pest.php`, el bootstrap que Pest exige en su directorio de pruebas por defecto.
