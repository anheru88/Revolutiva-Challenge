#!/usr/bin/env bash
# PayIn Platform — lanzador de un solo comando.
#
#   ./run.sh                build + arranca el stack (db, api)
#   ./run.sh down|--down    detiene y elimina los contenedores
#   ./run.sh logs           sigue los logs de los contenedores
#
# Para detener sin este script: `docker compose down`
# (añade `-v` para borrar también el volumen de la base de datos).
#
# Prepara los .env desde sus plantillas .env.example, rellena una APP_KEY de
# Laravel, levanta los contenedores y espera a que la API responda.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

# --- salida con color --------------------------------------------------------
info()  { printf '\033[1;34m›\033[0m %s\n' "$*"; }
ok()    { printf '\033[1;32m✓\033[0m %s\n' "$*"; }
warn()  { printf '\033[1;33m!\033[0m %s\n' "$*"; }
die()   { printf '\033[1;31m✗\033[0m %s\n' "$*" >&2; exit 1; }

# --- prerrequisitos ----------------------------------------------------------
command -v docker >/dev/null 2>&1 || die "docker no está instalado."
if docker compose version >/dev/null 2>&1; then
    COMPOSE=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
    COMPOSE=(docker-compose)
else
    die "se requiere docker compose (v2) o docker-compose."
fi

# --- subcomandos -------------------------------------------------------------
case "${1:-up}" in
    down|--down|-d) info "Deteniendo el stack…"; "${COMPOSE[@]}" down; ok "Detenido."; exit 0 ;;
    logs|--logs) exec "${COMPOSE[@]}" logs -f ;;
    up|"") ;;
    *) die "Comando desconocido '$1' (usa: up | down | logs)." ;;
esac

# --- preparación de .env -----------------------------------------------------
# ensure_env <plantilla> <destino>
ensure_env() {
    local example="$1" target="$2"
    [ -f "$example" ] || die "Falta la plantilla: $example"
    if [ -f "$target" ]; then
        ok "$target ya presente."
    else
        cp "$example" "$target"
        ok "Creado $target desde $(basename "$example")."
    fi
}

info "Preparando archivos de entorno…"
ensure_env ".env.example"         ".env"
ensure_env "backend/.env.example" "backend/.env"

# --- APP_KEY -----------------------------------------------------------------
# shellcheck disable=SC1091
set -a; . ./.env; set +a

if [ -z "${APP_KEY:-}" ]; then
    NEW_KEY="base64:$(openssl rand -base64 32)"
    if grep -q '^APP_KEY=' .env; then
        awk -v k="$NEW_KEY" 'BEGIN{FS=OFS="="} /^APP_KEY=/{print "APP_KEY=" k; next} {print}' .env > .env.tmp
        mv .env.tmp .env
    else
        printf 'APP_KEY=%s\n' "$NEW_KEY" >> .env
    fi
    APP_KEY="$NEW_KEY"
    ok "APP_KEY generada."
else
    ok "APP_KEY ya configurada."
fi

# --- arranque ----------------------------------------------------------------
info "Construyendo y arrancando los contenedores (la primera vez descarga imágenes y compila)…"
"${COMPOSE[@]}" up -d --build

# --- espera a que la API esté sana -------------------------------------------
info "Esperando a que la API responda (migraciones y seed corren en el primer arranque)…"
deadline=$(( $(date +%s) + 180 ))
until curl -fsS "http://localhost:${BACKEND_PORT:-8000}/up" >/dev/null 2>&1; do
    if [ "$(date +%s)" -ge "$deadline" ]; then
        warn "La API no reportó estar sana a tiempo. Revisa: ./run.sh logs"
        break
    fi
    sleep 3
done
ok "Stack levantado."

printf '\n'
ok   "API:            http://localhost:${BACKEND_PORT:-8000}/api/v1"
info "Health check:   http://localhost:${BACKEND_PORT:-8000}/up"
info "Sigue los logs: ./run.sh logs"
info "Detén todo:     ./run.sh down"
