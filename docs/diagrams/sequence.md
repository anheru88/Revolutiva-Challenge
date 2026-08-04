# Diagrama de Secuencia — Crear PayIn

Flujo del caso de uso `CreatePayIn` (`POST /api/v1/pay-ins`).

```mermaid
sequenceDiagram
    autonumber
    actor Client
    participant Ctrl as PayInController
    participant UC as CreatePayIn (UseCase)
    participant Repo as PayInRepository
    participant Res as ProviderResolver
    participant Adp as ProviderAdapter

    Client->>Ctrl: POST /api/v1/pay-ins (snake_case)
    Ctrl->>Ctrl: validar request (FormRequest)
    Ctrl->>UC: execute(dto)

    UC->>UC: validar reglas de negocio
    UC->>Res: resolve(provider_code)
    Res-->>UC: ProviderAdapter
    UC->>UC: crear PayIn (CREATED → VALIDATED)

    rect rgb(235, 245, 255)
    note over UC,Repo: Persistir ANTES de enviar al proveedor (tx atómica)
    UC->>Repo: save(payIn) + historial CREATED/VALIDATED
    end

    UC->>Adp: process(payIn)
    Adp-->>UC: provider_request / provider_response
    UC->>UC: transición → PROCESSED | FAILED

    rect rgb(235, 245, 255)
    note over UC,Repo: Actualizar estado final (tx atómica)
    UC->>Repo: update(payIn) + append status history
    end

    UC-->>Ctrl: PayIn (uuid, status)
    Ctrl-->>Client: 201 Created (JSON)
```

**Notas:**
- La validación de formato ocurre en el `FormRequest`; las reglas de negocio, en el dominio.
- **El orquestador persiste el PayIn (CREATED/VALIDATED) antes de enviar la transacción al proveedor.** Cada escritura es atómica (ver [ADR-009](../ADR.md#adr-009---operaciones-transaccionales)); la llamada al proveedor queda **fuera** de la transacción.
- Un fallo del proveedor lleva la transacción a `FAILED`, registrando igualmente `provider_response` y el historial.
