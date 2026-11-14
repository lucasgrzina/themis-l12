#!/usr/bin/env bash
#
# Restaura un dump de produccion en la base local/servidor y le aplica la
# estructura de Themis L12 (equivalente Linux de actualizar_desde_prod.ps1).
#
# Uso:
#   ./database/scripts/actualizar_desde_prod.sh -d /ruta/dump_prod.sql
#   ./database/scripts/actualizar_desde_prod.sh -d /ruta/dump.sql --seed-liquidacion
#
# ATENCION: BORRA y recrea la base indicada (por defecto `themis`) antes de importar.
# Luego corre `php artisan migrate` y al final exporta un dump de la base
# resultante SIEMPRE a public/adminer.sql (se sobrescribe). Ese archivo NO va a git (esta en
# .gitignore): se lleva a prod con database/scripts/subir_a_prod.ps1. Tiene datos reales:
# public/.htaccess bloquea su descarga por HTTP. El seeder del informe de liquidacion es OPCIONAL
# (solo con --seed-liquidacion).
#
set -euo pipefail

# --- parametros ---
DUMP=""
DATABASE="themis"
DB_USER="root"
ASSUME_YES=0
SEED_LIQUIDACION=0

usage() {
    echo "Uso: $0 -d <dump.sql> [-b base] [-u usuario] [--yes] [--seed-liquidacion]"
    exit 1
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        -d|--dump)             DUMP="$2"; shift 2 ;;
        -b|--database)         DATABASE="$2"; shift 2 ;;
        -u|--user)             DB_USER="$2"; shift 2 ;;
        -y|--yes)              ASSUME_YES=1; shift ;;
        --seed-liquidacion)    SEED_LIQUIDACION=1; shift ;;
        -h|--help)             usage ;;
        *) echo "Opcion desconocida: $1"; usage ;;
    esac
done

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
PUBLIC_DIR="$PROJECT_ROOT/public"
ESTRUCTURA="$SCRIPT_DIR/aplicar_estructura_l12_a_themis.sql"

[[ -n "$DUMP" ]]        || { echo "Falta -d <dump.sql>"; usage; }
[[ -f "$DUMP" ]]        || { echo "No existe el dump: $DUMP"; exit 1; }
[[ -f "$ESTRUCTURA" ]]  || { echo "No existe $ESTRUCTURA"; exit 1; }
command -v mysql >/dev/null     || { echo "No se encontro 'mysql' en el PATH"; exit 1; }
command -v mysqldump >/dev/null || { echo "No se encontro 'mysqldump' en el PATH"; exit 1; }

# --- password: se pide una vez y se usa via MYSQL_PWD (vacio = auth socket/.my.cnf) ---
read -rs -p "Password de MySQL para '$DB_USER' (ENTER si usa socket/.my.cnf): " DB_PWD
echo
if [[ -n "$DB_PWD" ]]; then
    export MYSQL_PWD="$DB_PWD"
fi
MYSQL=(mysql -u"$DB_USER" --default-character-set=utf8mb4)
MYSQLDUMP=(mysqldump -u"$DB_USER" --default-character-set=utf8mb4 --single-transaction --routines --triggers)

if [[ "$ASSUME_YES" -ne 1 ]]; then
    read -r -p "Se va a BORRAR la base '$DATABASE' y restaurar '$DUMP'. Escribi SI para continuar: " R
    [[ "$R" == "SI" ]] || { echo "Cancelado."; exit 1; }
fi

echo "1/6 Recreando base '$DATABASE'..."
"${MYSQL[@]}" -e "DROP DATABASE IF EXISTS \`$DATABASE\`; CREATE DATABASE \`$DATABASE\` CHARACTER SET utf8mb4"

echo "2/6 Importando dump (puede tardar)..."
# El dump (Adminer) trae DROP/CREATE DATABASE y USE <otra_base>: se quitan para
# que se importe en $DATABASE y no pise la base original del dump.
grep -vE '^(DROP DATABASE |CREATE DATABASE |USE )' "$DUMP" | "${MYSQL[@]}" "$DATABASE"

echo "3/6 Aplicando estructura L12..."
"${MYSQL[@]}" "$DATABASE" < "$ESTRUCTURA"

echo "4/6 Corriendo migraciones pendientes..."
( cd "$PROJECT_ROOT" && php artisan migrate --force )

if [[ "$SEED_LIQUIDACION" -eq 1 ]]; then
    echo "5/6 Sembrando parametrizacion del informe de liquidacion..."
    ( cd "$PROJECT_ROOT" && php artisan db:seed --class=AgostoLiquidacionSeeder --force )
else
    echo "5/6 Seeder de liquidacion OMITIDO (pasa --seed-liquidacion para incluirlo)."
fi

echo "6/6 Exportando dump a public/adminer.sql..."
mkdir -p "$PUBLIC_DIR"
SALIDA="$PUBLIC_DIR/adminer.sql"
"${MYSQLDUMP[@]}" "$DATABASE" > "$SALIDA"

echo "Listo: '$DATABASE' tiene los datos de prod + estructura L12."
echo "Dump generado: $SALIDA"
