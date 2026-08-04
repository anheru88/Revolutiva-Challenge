# Diagrama de Secuencia — Crear PayIn

Flujo del caso de uso `CreatePayIn` (`POST /api/v1/pay-ins`).

```mermaid
sequenceDiagram
    autonumber
    actor Client
    participant Ctrl as PayInController
    participant UC as CreatePayIn (UseCase)
    participant Fac as PayInFactory
    participant Repo as PayInRepository
    participant Proc as ProcessPayIn (UseCase)
    participant Res as ProviderResolver
    participant Adp as ProviderAdapter

    Client->>Ctrl: POST /api/v1/pay-ins (snake_case)
    Ctrl->>Ctrl: validar request (FormRequest)
    Ctrl->>UC: execute(dto)

    UC->>UC: validar reglas de negocio
    UC->>Res: resolve(provider_code) — comprobar que hay adaptador
    Res-->>UC: ProviderAdapter
    UC->>Fac: forNewTransaction(entidades, amount)
    Fac-->>UC: PayIn (CREATED)
    UC->>UC: markValidated() → VALIDATED

    rect rgb(235, 245, 255)
    note over UC,Repo: Persistir ANTES de enviar al proveedor (tx atómica)
    UC->>Repo: save(payIn) + historial CREATED/VALIDATED
    end

    note over UC,Proc: Síncrono hoy; ProcessPayInJob::dispatch(uuid) lo haría asíncrono (ADR-004)
    UC->>Proc: handle(uuid)
    Proc->>Repo: findByUuid(uuid)
    Repo-->>Proc: PayIn (VALIDATED)
    Proc->>Res: resolve(provider_code)
    Res-->>Proc: ProviderAdapter
    Proc->>Adp: process(payIn)
    Adp-->>Proc: provider_request / provider_response
    Proc->>Proc: transición → PROCESSED | FAILED

    rect rgb(235, 245, 255)
    note over Proc,Repo: Actualizar estado final (tx atómica)
    Proc->>Repo: update(payIn) + append status history
    end

    UC-->>Ctrl: PayIn (uuid, status)
    Ctrl-->>Client: 201 Created (JSON)
```

**Notas:**
- La validación de formato ocurre en el `FormRequest`; las reglas de negocio, en el dominio.
- **El orquestador persiste el PayIn (CREATED/VALIDATED) antes de enviar la transacción al proveedor.** Cada escritura es atómica (ver [ADR-009](../ADR.md#adr-009---operaciones-transaccionales)); la llamada al proveedor queda **fuera** de la transacción.
- Un fallo del proveedor lleva la transacción a `FAILED`, registrando igualmente `provider_response` y el historial.
- El paso de proveedor es un caso de uso aparte (`ProcessPayIn`) que recibe un UUID y recarga el agregado: el mismo camino sirve en línea o despachado en cola por `ProcessPayInJob` (ver [ADR-004](../ADR.md#adr-004---procesamiento-síncrono-con-diseño-listo-para-asíncrono)).
