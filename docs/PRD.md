# PRD - PayIn Platform Component

## 1. Descripción

Desarrollar un componente reusable para el procesamiento de transacciones **PayIn**, siguiendo una Arquitectura Hexagonal, permitiendo soportar múltiples proveedores de pago mediante adaptadores desacoplados.

El componente será responsable del ciclo completo de vida de una transacción:

- Crear
- Validar
- Procesar
- Persistir
- Consultar
- Gestionar estados

La solución debe demostrar buenas prácticas de arquitectura, modelado de dominio y extensibilidad.

---

# 2. Objetivos

## Funcionales

- Crear una transacción PayIn.
- Validar reglas de negocio.
- Persistir la información.
- Procesar la transacción utilizando un proveedor.
- Actualizar el estado de la transacción.
- Consultar una transacción por su identificador.

## No Funcionales

- Arquitectura Hexagonal.
- Dominio desacoplado de Laravel.
- Código testeable.
- Fácil incorporación de nuevos proveedores.
- API REST versionada.

---

# 3. Alcance

## Incluye

- API REST
- Persistencia con Eloquent
- Adaptadores de proveedores
- Base de datos relacional
- Validaciones
- Documentación
- Pruebas unitarias e integración

## No Incluye

- Frontend
- Autenticación
- Autorización
- Integraciones reales con gateways

---

# 4. Arquitectura

Se implementará una Arquitectura Hexagonal compuesta por:

- Domain
- Application
- Infrastructure
- Shared

El dominio no dependerá de Laravel ni de infraestructura.

---

# 5. Modelo de Dominio

## Entidades

- Customer
- Account
- PaymentMethod
- PaymentProvider
- PayIn

## Value Objects

- Money
- Email
- UUID

## Estados

- CREATED
- VALIDATED
- PROCESSED
- FAILED

---

# 6. Relaciones

- Un Customer puede tener múltiples Accounts.
- Una Account puede tener múltiples PaymentMethods.
- Un PaymentMethod puede ser procesado por uno o varios PaymentProviders.
- Un PayIn pertenece a:
  - Customer
  - Account
  - PaymentMethod
  - PaymentProvider

---

# 7. Casos de Uso

## Crear PayIn

1. Validar Request.
2. Validar reglas de negocio.
3. Crear la transacción con estado CREATED.
4. Persistir.
5. Resolver el proveedor.
6. Procesar mediante el Adapter.
7. Actualizar estado.
8. Retornar respuesta.

## Consultar PayIn

Consultar una transacción mediante su UUID.

---

# 8. API

## POST /api/v1/pay-ins

Crear una transacción.

## GET /api/v1/pay-ins/{uuid}

Consultar una transacción.

Todos los campos utilizarán snake_case.

---

# 9. Base de Datos

## customers

- id
- uuid
- name
- email

## accounts

- id
- uuid
- customer_id
- account_number

## payment_methods

- id
- uuid
- account_id
- type

## payment_providers

- id
- code
- name

## pay_ins

- id
- uuid
- customer_id
- account_id
- payment_method_id
- payment_provider_id
- amount
- currency
- status
- provider_request
- provider_response
- created_at
- updated_at

## pay_in_status_history

- id
- pay_in_id
- previous_status
- current_status
- created_at

---

# 10. Decisiones Arquitectónicas

## Selección de proveedor

Se utilizará un **ProviderResolver**, responsable de seleccionar el adaptador correspondiente según el proveedor indicado.

## Procesamiento

Inicialmente será síncrono, pero el diseño permitirá migrar fácilmente a procesamiento asíncrono mediante Jobs.

## Identificadores

- ID entero para relaciones internas.
- UUID como identificador público.

## Auditoría

Se almacenará:

- Request enviado al proveedor.
- Response recibido.
- Historial de cambios de estado.

---

# 11. Patrones de Diseño

- Repository
- Strategy
- Factory
- Adapter
- DTO
- Value Objects
- Dependency Injection

---

# 12. Estructura del Proyecto

```
src/

PayIn/
    Domain/
    Application/
    Infrastructure/

Shared/

routes/
tests/
database/
```

---

# 13. Testing

- Unit Tests
- Feature Tests
- Cobertura objetivo: 80%

---

# 14. Calidad

Herramientas propuestas:

- Pest
- PHPStan
- Larastan
- Laravel Pint
- GitHub Actions

---

# 15. Entregables

- Código fuente.
- Scripts SQL.
- README.
- Diagrama de arquitectura.
- Diagrama del dominio.
- Colección Postman.
- Pipeline de CI.
- Documentación de decisiones técnicas.

---

# 16. Supuestos

- El proveedor de pago será indicado en el request.
- No se implementarán integraciones reales.
- Se utilizarán adaptadores simulados.
- Todas las operaciones serán transaccionales.
- No se contemplan reintentos automáticos.
- No se implementará autenticación.

---

# 17. Riesgos

- Cambios futuros en contratos de proveedores.
- Reglas de negocio no definidas.
- Procesamiento asíncrono fuera del alcance.
- Integraciones reales pueden requerir nuevos adaptadores.

---

# 18. Criterios de Éxito

- Arquitectura desacoplada.
- Dominio independiente.
- Código limpio y testeable.
- Fácil incorporación de nuevos proveedores.
- API consistente.
- Modelo relacional normalizado.
- Documentación suficiente para comprender y extender el componente.
