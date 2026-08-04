# Diagrama de Arquitectura — Hexagonal (Ports & Adapters)

El dominio no depende de Laravel ni de infraestructura. Los adaptadores de entrada (API) invocan los casos de uso de la capa de Aplicación; los adaptadores de salida (persistencia y proveedores) implementan los puertos definidos por la Aplicación.

```mermaid
flowchart LR
    subgraph IN["Infrastructure — Driving (Inbound)"]
        API["API REST v1<br/>PayInController<br/>FormRequests<br/>POST /api/v1/pay-ins<br/>GET /api/v1/pay-ins/{uuid}"]
    end

    subgraph APP["Application"]
        UC["Casos de uso<br/>CreatePayIn · ProcessPayIn · GetPayIn"]
        PORTS["Puertos (interfaces)<br/>PayInRepositoryPort<br/>PaymentProviderPort"]
        RES["ProviderResolver<br/>(Factory + Strategy)"]
        DTO["DTOs"]
    end

    subgraph DOM["Domain (PHP puro)"]
        ENT["Entidades<br/>PayIn · Customer · Account<br/>PaymentMethod · PaymentProvider"]
        FAC["PayInFactory<br/>(Factory del agregado)"]
        VO["Value Objects<br/>Money · Email · UUID"]
        ST["PayInStatus<br/>CREATED/VALIDATED/PROCESSED/FAILED"]
    end

    subgraph OUT["Infrastructure — Driven (Outbound)"]
        REPO["PayInRepository<br/>(Eloquent + Mappers)"]
        ADPA["ProviderAdapter A<br/>(simulado)"]
        ADPB["ProviderAdapter B<br/>(simulado)"]
        JOB["ProcessPayInJob<br/>(cola — camino asíncrono, ADR-004)"]
    end

    SHARED["Shared<br/>VO base · excepciones · contratos comunes"]

    API --> UC
    JOB --> UC
    UC --> ENT
    UC --> FAC
    UC --> PORTS
    UC --> RES
    RES --> ADPA
    RES --> ADPB
    REPO -. implements .-> PORTS
    ADPA -. implements .-> PORTS
    ADPB -. implements .-> PORTS

    DOM -. usa .- SHARED
    APP -. usa .- SHARED

    classDef domain fill:#d5e8d4,stroke:#82b366;
    classDef app fill:#dae8fc,stroke:#6c8ebf;
    classDef infra fill:#ffe6cc,stroke:#d79b00;
    classDef shared fill:#f8cecc,stroke:#b85450;
    class ENT,FAC,VO,ST domain;
    class UC,PORTS,RES,DTO app;
    class API,REPO,ADPA,ADPB,JOB infra;
    class SHARED shared;
```

**Regla de dependencias:** las flechas de infraestructura apuntan *hacia adentro*. `Infrastructure → Application → Domain`. El dominio no conoce a nadie fuera de sí mismo.

**Empaquetado:** todo lo anterior vive en `backend/src`, que es un paquete Composer (`revolutiva/payin`) con sus migraciones y sus pruebas dentro — ver [ADR-011](../ADR.md#adr-011---el-módulo-es-un-paquete-composer).
