# Diagrama del Dominio

Entidades, Value Objects y máquina de estados. `PayIn` es la raíz del agregado.

```mermaid
classDiagram
    class PayIn {
        <<Aggregate Root>>
        +UUID uuid
        +int customerId
        +int accountId
        +int paymentMethodId
        +int paymentProviderId
        +Money amount
        +PayInStatus status
        +array providerRequest
        +array providerResponse
        +validate()
        +markProcessed()
        +markFailed()
        +transitionTo(status)
    }

    class Customer {
        +UUID uuid
        +string name
        +Email email
    }

    class Account {
        +UUID uuid
        +int customerId
        +string accountNumber
    }

    class PaymentMethod {
        +UUID uuid
        +int accountId
        +string type
    }

    class PaymentProvider {
        +string code
        +string name
    }

    class Money {
        <<Value Object>>
        +int amount
        +string currency
        +equals(Money) bool
    }

    class Email {
        <<Value Object>>
        +string value
    }

    class UUID {
        <<Value Object>>
        +string value
        +generate() UUID
    }

    class PayInStatus {
        <<enumeration>>
        CREATED
        VALIDATED
        PROCESSED
        FAILED
    }

    Customer "1" --> "N" Account
    Account "1" --> "N" PaymentMethod
    PaymentMethod "N" ..> "N" PaymentProvider : procesado por
    PayIn --> Customer
    PayIn --> Account
    PayIn --> PaymentMethod
    PayIn --> PaymentProvider
    PayIn *-- Money : amount
    PayIn --> PayInStatus : status
    Customer *-- Email
```

## Máquina de estados

```mermaid
stateDiagram-v2
    [*] --> CREATED
    CREATED --> VALIDATED : reglas de negocio OK
    VALIDATED --> PROCESSED : proveedor responde OK
    VALIDATED --> FAILED : proveedor rechaza / error
    CREATED --> FAILED : validación falla
    PROCESSED --> [*]
    FAILED --> [*]
```

**Notas:**
- `FAILED` es terminal; no admite reintentos automáticos (fuera de alcance según el PRD).
- Las transiciones se validan en el dominio (ver [ADR-007](../ADR.md#adr-007---máquina-de-estados-de-payin)).
