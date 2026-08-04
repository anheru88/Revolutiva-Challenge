# PayIn Platform

Componente reusable para el procesamiento de transacciones **PayIn**, construido con **Arquitectura Hexagonal** sobre **Laravel 13** y **PHP 8.4+**. Soporta múltiples proveedores de pago mediante adaptadores desacoplados y mantiene el dominio independiente del framework.

## Estructura del repositorio

```
.
├── docs/
│   ├── PRD.md                 # Product Requirements Document
│   ├── ADR.md                 # Architecture Decision Records (ADR-001..009)
│   ├── postman/               # Colección Postman
│   └── diagrams/              # Diagramas en Markdown (Mermaid)
│       ├── architecture.md
│       ├── sequence.md
│       ├── er.md
│       └── domain.md
├── backend/                   # Aplicación Laravel (API REST v1)
│   ├── src/                   # Código hexagonal (namespace Src\)
│   └── Dockerfile             # Imagen del backend (multi-stage)
├── docker-compose.yml         # Stack: MariaDB + API
├── run.sh                     # Lanzador de un comando (build + up)
├── DOCKER.md                  # Guía de Docker
├── .env.example               # Variables de orquestación (Compose)
├── .github/workflows/ci.yml   # Pipeline CI (Pint · PHPStan · Pest)
└── README.md
```

## Arquitectura

Arquitectura Hexagonal (Ports & Adapters). El dominio **no** depende de Laravel ni de infraestructura; la comunicación con el exterior pasa por puertos (interfaces) y adaptadores. Ver [`docs/ADR.md`](docs/ADR.md).

```
backend/src/
├── PayIn/
│   ├── Domain/               # Entidades, VOs, enum de estados, puertos de repositorio
│   │   ├── Entity/           #   PayIn (aggregate root), Customer, Account, ...
│   │   ├── Enum/             #   PayInStatus (+ transiciones válidas)
│   │   ├── ValueObject/      #   StatusTransition
│   │   ├── Repository/       #   Puertos (interfaces)
│   │   └── Exception/
│   ├── Application/          # Casos de uso, comandos, DTOs de lectura, ProviderResolver
│   │   ├── UseCase/          #   CreatePayInHandler, GetPayInHandler
│   │   ├── Command/ Query/   #   CreatePayInCommand, PayInResponse, puerto de lectura
│   │   └── Provider/         #   PaymentProviderPort, ProviderResolver, ProviderResult
│   └── Infrastructure/       # Adaptadores concretos
│       ├── Http/             #   Controller, FormRequest, Resource
│       ├── Persistence/      #   Modelos Eloquent, repositorios, mappers
│       ├── Provider/         #   Adaptadores de proveedor (simulados)
│       └── Laravel/          #   PayInServiceProvider (bindings puerto→adaptador)
└── Shared/                   # VOs reutilizables (Money, Email, Uuid), excepciones, TransactionManager
```

**Regla de dependencias:** `Infrastructure → Application → Domain`. Los VOs comunes viven en `Shared`.

## API

| Método | Endpoint                 | Descripción                        |
| ------ | ------------------------ | ---------------------------------- |
| POST   | `/api/v1/pay-ins`        | Crear una transacción PayIn        |
| GET    | `/api/v1/pay-ins/{uuid}` | Consultar una transacción por UUID |

Todos los campos de request/response usan `snake_case`.

### Ejemplo — crear PayIn

```bash
curl -X POST http://localhost:8000/api/v1/pay-ins \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{
    "customer_uuid": "11111111-1111-4111-8111-111111111111",
    "account_uuid": "22222222-2222-4222-8222-222222222222",
    "payment_method_uuid": "33333333-3333-4333-8333-333333333333",
    "provider_code": "provider_a",
    "amount": 15000,
    "currency": "USD"
  }'
```

```jsonc
// 201 Created
{
  "data": {
    "uuid": "…",
    "customer_uuid": "11111111-…",
    "account_uuid": "22222222-…",
    "payment_method_uuid": "33333333-…",
    "provider_code": "provider_a",
    "amount": 15000,
    "currency": "USD",
    "status": "PROCESSED",
    "provider_request":  { "…": "…" },
    "provider_response": { "status": "approved", "…": "…" },
    "created_at": "2026-08-04T…",
    "updated_at": "2026-08-04T…"
  }
}
```

Los UUID de ejemplo provienen del `PayInReferenceSeeder`. El adaptador `provider_b` **rechaza** importes por encima de un límite simulado (`> 1000000`), dejando la transacción en estado `FAILED`.

### Códigos de respuesta

| Código | Situación                                                        |
| ------ | ---------------------------------------------------------------- |
| 201    | PayIn creado (estado final `PROCESSED` o `FAILED`)               |
| 200    | Consulta correcta                                                |
| 404    | Customer/Account/PaymentMethod/Provider o PayIn no encontrado    |
| 422    | Error de validación o violación de regla de negocio              |

## Estados de una transacción

`CREATED → VALIDATED → PROCESSED`, con `FAILED` como estado terminal de error. Cada transición se registra en `pay_in_status_history` (ver [ADR-007](docs/ADR.md#adr-007---máquina-de-estados-de-payin)).

## Puesta en marcha con Docker (recomendado)

Un solo comando construye y levanta el stack (MariaDB + API). Migraciones y
datos de referencia corren automáticamente en el primer arranque.

```bash
./run.sh          # build + up, espera a que la API responda
./run.sh logs     # sigue los logs
./run.sh down     # detiene y elimina los contenedores
```

API en `http://localhost:8000/api/v1` (health check en `/up`). Detalles y
configuración en [`DOCKER.md`](DOCKER.md).

## Puesta en marcha manual (sin Docker)

Requisitos: **PHP 8.4+** y **Composer 2.x**.

```bash
cd backend
cp .env.example .env
php artisan key:generate
php artisan migrate --seed    # crea tablas + datos de referencia
php artisan serve             # http://localhost:8000
```

Por defecto usa SQLite; ajusta las variables `DB_*` en `.env` para otra base.

El esquema relacional se define en las migraciones (`backend/database/migrations`),
que son la única fuente de verdad. Para obtener un volcado SQL a partir de ellas:

```bash
php artisan schema:dump       # genera database/schema/<conexión>-schema.sql
```

## Testing y calidad

Cobertura objetivo: **80%** (exigida como gate en CI).

```bash
cd backend
composer test        # Pest
composer lint        # Laravel Pint (--test)
composer analyse     # PHPStan / Larastan (nivel 6)
composer check       # lint + analyse + test
```

Herramientas: **Pest**, **Laravel Pint**, **PHPStan/Larastan** y **GitHub Actions** (`.github/workflows/ci.yml`).

## Documentación

- [PRD](docs/PRD.md) — requisitos del producto.
- [ADR](docs/ADR.md) — decisiones arquitectónicas.
- [Diagramas](docs/diagrams/) — arquitectura, secuencia, ER y dominio.
- Esquema relacional — definido en `backend/database/migrations` (generable con `php artisan schema:dump`).
- [Postman](docs/postman/) — colección de la API.
