# PayIn Platform

Componente reusable para el procesamiento de transacciones **PayIn**, construido con **Arquitectura Hexagonal** sobre **Laravel 13** y **PHP 8.5**. Soporta múltiples proveedores de pago mediante adaptadores desacoplados y mantiene el dominio independiente del framework.

## Estructura del repositorio

```
.
├── docs/
│   ├── PRD.md                 # Product Requirements Document
│   ├── ADR.md                 # Architecture Decision Records
│   └── diagrams/              # Diagramas en Markdown (Mermaid)
│       ├── architecture.md
│       ├── sequence.md
│       ├── er.md
│       └── domain.md
├── backend/                   # Aplicación Laravel (API REST v1)
└── README.md
```

## Arquitectura

Arquitectura Hexagonal (Ports & Adapters) con cuatro capas. El dominio **no** depende de Laravel ni de infraestructura.

- **Domain** — Entidades (`PayIn`, `Customer`, `Account`, `PaymentMethod`, `PaymentProvider`), Value Objects (`Money`, `Email`, `UUID`) y la máquina de estados.
- **Application** — Casos de uso (`CreatePayIn`, `GetPayIn`), puertos, DTOs y `ProviderResolver`.
- **Infrastructure** — Adaptadores: API REST, persistencia Eloquent y adaptadores de proveedores (simulados).
- **Shared** — Contratos y utilidades comunes.

Detalle y justificación de cada decisión en [`docs/ADR.md`](docs/ADR.md).

## API

| Método | Endpoint                    | Descripción                       |
| ------ | --------------------------- | --------------------------------- |
| POST   | `/api/v1/pay-ins`           | Crear una transacción PayIn       |
| GET    | `/api/v1/pay-ins/{uuid}`    | Consultar una transacción por UUID|

Todos los campos de request/response usan `snake_case`.

## Estados de una transacción

`CREATED → VALIDATED → PROCESSED`, con `FAILED` como estado terminal de error. Cada transición se registra en `pay_in_status_history`.

## Requisitos

- PHP 8.5+
- Composer 2.x

## Puesta en marcha (backend)

```bash
cd backend
cp .env.example .env      # ya creado por el instalador
php artisan key:generate
php artisan migrate
php artisan serve
```

## Testing y calidad

Herramientas previstas: **Pest**, **PHPStan/Larastan**, **Laravel Pint** y **GitHub Actions**. Cobertura objetivo: **80%**.

```bash
cd backend
php artisan test
```

## Documentación

- [PRD](docs/PRD.md) — requisitos del producto.
- [ADR](docs/ADR.md) — decisiones arquitectónicas.
- [Diagramas](docs/diagrams/) — arquitectura, secuencia, ER y dominio.
