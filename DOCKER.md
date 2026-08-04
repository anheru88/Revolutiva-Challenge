# Ejecutar PayIn Platform con Docker

Un solo comando construye y sirve todo el stack — MariaDB y la API de Laravel:

```bash
./run.sh
```

Luego abre **http://localhost:8000/api/v1** (health check en `/up`).

## Qué hace `./run.sh`

1. Copia cada `.env.example` a `.env` si aún no existe (raíz y `backend/`).
2. Genera una `APP_KEY` de Laravel en el `.env` raíz cuando está vacía.
3. Ejecuta `docker compose up -d --build`.
4. Espera a que la API responda en `/up`. Las migraciones y el seed de datos de
   referencia corren automáticamente en el primer arranque.

Otros subcomandos:

```bash
./run.sh logs   # sigue los logs de los contenedores
./run.sh down   # detiene y elimina los contenedores
```

## El stack

| Servicio  | Imagen / build                      | Puerto (host) | Rol |
|-----------|-------------------------------------|---------------|-----|
| `db`      | `mariadb:11`                        | `3307`        | Base de datos, datos en un volumen con nombre |
| `backend` | `backend/Dockerfile` (php 8.4 cli)  | `8000`        | API de Laravel (`artisan serve`, 4 workers) |

La imagen del backend es multi-stage: una etapa `composer:2` instala las
dependencias de producción (`--no-dev`) y la etapa final `php:8.4-cli-alpine`
añade las extensiones `pdo_mysql` y `mbstring`. El `docker/entrypoint.sh` espera
a la base de datos, aplica `migrate --force`, siembra los datos de referencia
solo si la base está vacía y arranca el servidor.

Documentación de la API (Scramble): **http://localhost:8000/docs/api** (Swagger UI)
y **http://localhost:8000/docs/api.json** (OpenAPI 3.1).

## Desarrollo vs. producción (¿por qué no reconstruir en cada cambio?)

Por defecto, `docker compose up` (y `./run.sh`) fusiona
`docker-compose.override.yml`, que **monta el código fuente como volumen**
(`./backend:/var/www/html`). Así, los cambios de código se reflejan al instante
sin reconstruir la imagen; `vendor` y `bootstrap/cache` se conservan desde la
imagen mediante volúmenes anónimos. Este modo corre con `APP_ENV=local`.

Solo hace falta reconstruir (`--build`) al cambiar **dependencias**
(`composer require`) o el propio `Dockerfile`.

Para levantar el modo producción (imagen inmutable horneada, sin bind mount):

```bash
docker compose -f docker-compose.yml up -d --build
```

## Configuración

Todas las variables viven en el `.env` raíz (creado desde `.env.example`):

- `BACKEND_PORT`, `DB_PORT` — puertos del host para la API y la base de datos.
  `DB_PORT` viene en `3307` para no chocar con un MySQL/MariaDB local en el 3306;
  ajústalo si tienes esos puertos ocupados.
- `DB_*` — nombre de la base y credenciales de MariaDB.
- `APP_KEY` — la rellena `./run.sh`.

El contenedor backend lee `backend/.env` para su config base de Laravel; el
`docker-compose.yml` sobreescribe la conexión de base de datos y algunos ajustes
de producción por encima (el dotenv de Laravel es inmutable, así que las
variables de entorno del contenedor ganan).

## Notas

- `artisan serve` con `PHP_CLI_SERVER_WORKERS=4` se usa por simplicidad para la
  demo. Para producción real, cámbialo por php-fpm + nginx o FrankenPHP.
- Los datos de referencia se siembran solo cuando la base está vacía, así los
  reinicios no los duplican. Para empezar de cero:
  `./run.sh down && docker volume rm revolutiva_db-data`.
