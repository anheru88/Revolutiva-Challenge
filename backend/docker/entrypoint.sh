#!/bin/sh
# Arranque del contenedor backend: espera la base de datos, aplica las
# migraciones, siembra los datos de referencia una sola vez y sirve la API.
set -e
cd /var/www/html

echo "[entrypoint] waiting for database at ${DB_HOST}:${DB_PORT:-3306} ..."
until php -r '
    try {
        new PDO(
            sprintf("mysql:host=%s;port=%s", getenv("DB_HOST"), getenv("DB_PORT") ?: "3306"),
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD")
        );
    } catch (Throwable $e) {
        exit(1);
    }
'; do
    sleep 2
done
echo "[entrypoint] database is up."

# Debe existir una APP_KEY. Compose normalmente la suministra (./run.sh la
# rellena); si no, se genera una efímera solo en el entorno de este proceso,
# ya que no hay .env dentro de la imagen donde escribirla.
if [ -z "${APP_KEY}" ]; then
    echo "[entrypoint] APP_KEY empty — generating an ephemeral key."
    APP_KEY="$(php artisan key:generate --show)"
    export APP_KEY
fi

php artisan migrate --force

# Siembra solo cuando no hay datos de referencia, para que los reinicios no
# dupliquen la información.
CUSTOMERS="$(php artisan tinker --execute="echo DB::table('customers')->count();" 2>/dev/null | tail -n 1 | tr -dc '0-9')"
if [ -z "${CUSTOMERS}" ] || [ "${CUSTOMERS}" = "0" ]; then
    echo "[entrypoint] empty database — seeding reference data."
    php artisan db:seed --force
else
    echo "[entrypoint] ${CUSTOMERS} customers present — skipping seed."
fi

echo "[entrypoint] serving API on :8000"
# --no-reload permite que artisan respete PHP_CLI_SERVER_WORKERS, de modo que el
# servidor integrado atienda peticiones de forma concurrente.
exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
