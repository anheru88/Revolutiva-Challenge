# Diagrama Entidad-Relación

Modelo relacional normalizado. `id` entero para relaciones internas; `uuid` como identificador público (ver [ADR-005](../ADR.md#adr-005---identificadores-duales-id-interno--uuid-público)).

```mermaid
erDiagram
    customers ||--o{ accounts : "tiene"
    accounts ||--o{ payment_methods : "tiene"
    customers ||--o{ pay_ins : "origina"
    accounts ||--o{ pay_ins : "asociada"
    payment_methods ||--o{ pay_ins : "usada en"
    payment_providers ||--o{ pay_ins : "procesa"
    pay_ins ||--o{ pay_in_status_history : "registra"

    customers {
        bigint id PK
        uuid uuid UK
        string name
        string email
    }

    accounts {
        bigint id PK
        uuid uuid UK
        bigint customer_id FK
        string account_number
    }

    payment_methods {
        bigint id PK
        uuid uuid UK
        bigint account_id FK
        string type
    }

    payment_providers {
        bigint id PK
        string code UK
        string name
    }

    pay_ins {
        bigint id PK
        uuid uuid UK
        bigint customer_id FK
        bigint account_id FK
        bigint payment_method_id FK
        bigint payment_provider_id FK
        bigint amount
        string currency
        string status
        json provider_request
        json provider_response
        timestamp created_at
        timestamp updated_at
    }

    pay_in_status_history {
        bigint id PK
        bigint pay_in_id FK
        string previous_status
        string current_status
        timestamp created_at
    }
```

**Notas:**
- `amount` se almacena en la unidad menor de la moneda (entero) para evitar errores de punto flotante (VO `Money`).
- `pay_ins.status` refleja el estado vigente; `pay_in_status_history` conserva la traza completa de transiciones.
