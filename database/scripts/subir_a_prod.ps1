<#
.SYNOPSIS
  Sube el dump generado por actualizar_desde_prod.ps1 al servidor de PRODUCCION por SSH y
  REEMPLAZA la base de prod con ese dump (con respaldo previo en el servidor).

.DESCRIPTION
  Flujo:
    1. Valida el dump local (public\adminer.sql): que exista y que este completo
       (mysqldump termina con "-- Dump completed"). Muestra tamanio y fecha.
    2. Lo comprime (.gz) en una carpeta temporal.
    3. Lee DB_DATABASE del .env del servidor y te pide escribir ese nombre para confirmar.
    4. Sube el .gz y un script de import por scp/pscp (a /tmp, permisos 600/700).
    5. En el servidor (las credenciales salen del .env del servidor, nunca viajan por la
       linea de comandos ni se guardan aca):
         a) respalda la base de prod en ~/backups/themis/prod_<base>_<fecha>.sql.gz (home del usuario SSH)
            y verifica el respaldo; si falla, ABORTA sin tocar nada;
         b) borra todas las tablas/vistas de la base e importa el dump
            (no necesita permiso CREATE/DROP DATABASE);
         c) imprime un conteo de control y borra los archivos temporales.
    Si el import falla, el script te dice el comando para restaurar el respaldo.

  NO hace: git pull, migrate, limpiar cache ni modo mantenimiento.
  ATENCION: reemplaza TODA la base de prod. Lo cargado en prod despues de tomarse el dump
  se pierde (queda solo en el respaldo del servidor).

.EXAMPLE
  .\database\scripts\subir_a_prod.ps1

.EXAMPLE
  # Solo valida el dump y comprime; no se conecta al servidor
  .\database\scripts\subir_a_prod.ps1 -DryRun
#>
param(
    [string]$Target = 'ubuntu@168.205.94.185',
    [string]$RemotePath = '/var/www/html/themis',
    [string]$Dump = (Join-Path $PSScriptRoot '..\..\public\adminer.sql'),
    # Clave privada. Si termina en .ppk se usa PuTTY (plink/pscp); si no, OpenSSH (ssh/scp -i).
    # Vacio = OpenSSH con la clave/agente por defecto.
    [string]$Key = 'C:\AmazonKeys\baehost.ppk',
    # Solo comprueba el servidor (conexion, .env, herramientas, permisos, estado de datos). No sube ni modifica nada.
    [switch]$Preflight,
    # Equivale a escribir el nombre de la base cuando el script lo pide (para correrlo sin teclado).
    # Si no coincide con DB_DATABASE del servidor, aborta sin tocar nada.
    [string]$Confirmar,
    [switch]$DryRun
)

$ErrorActionPreference = 'Stop'

$usePutty = [bool]($Key -and $Key -like '*.ppk')
$tools = if ($usePutty) { 'plink', 'pscp' } else { 'ssh', 'scp' }
foreach ($cmd in $tools) {
    if (-not (Get-Command $cmd -ErrorAction SilentlyContinue)) { throw "No se encontro '$cmd' en el PATH." }
}
if ($Key -and -not (Test-Path $Key)) { throw "No existe la clave: $Key" }

# Ejecuta un comando en el servidor / copia un archivo al servidor (PuTTY u OpenSSH segun la clave).
# Los comandos remotos no llevan comillas dobles (evita problemas de quoting en PowerShell 5.1).
function Invoke-Remote([string]$cmd) {
    if ($usePutty) { & plink -batch -i $Key $Target $cmd }
    else {
        $o = @('-o', 'ConnectTimeout=15'); if ($Key) { $o += @('-i', $Key) }
        & ssh @o $Target $cmd
    }
}
function Copy-Remote([string]$src, [string]$dst) {
    if ($usePutty) { & pscp -batch -q -i $Key $src "${Target}:$dst" }
    else {
        $o = @('-o', 'ConnectTimeout=15'); if ($Key) { $o += @('-i', $Key) }
        & scp @o $src "${Target}:$dst"
    }
}

# ---- Preflight: solo lectura en el servidor ----
if ($Preflight) {
    Write-Host "Preflight en $Target (solo lectura)..."
    $pre = @'
APP='__APP__'
test -f $APP/.env && echo '.env: existe' || { echo '.env: NO existe'; exit 1; }
v() { grep -E "^$1=" $APP/.env | tail -1 | cut -d= -f2- | tr -d '\042\r'; }
for k in DB_HOST DB_DATABASE DB_USERNAME DB_PASSWORD; do [ -n "$(v $k)" ] && echo "  $k: definido" || echo "  $k: FALTA"; done
echo "base: $(v DB_DATABASE) en $(v DB_HOST) | APP_ENV=$(v APP_ENV)"
for c in mysql mysqldump gzip zcat; do command -v $c >/dev/null && echo "$c: ok" || echo "$c: FALTA"; done
test -w "$HOME" && echo "respaldos en: $HOME/backups/themis (HOME escribible)" || echo "HOME NO escribible"
export MYSQL_PWD="$(v DB_PASSWORD)"
mysql -h"$(v DB_HOST)" -u"$(v DB_USERNAME)" "$(v DB_DATABASE)" -N -B -e 'SELECT COUNT(*) FROM users' >/dev/null 2>&1 && echo 'conexion MySQL: ok' || echo 'conexion MySQL: FALLA'
df -h /tmp | tail -1
'@
    Invoke-Remote (($pre.Replace('__APP__', $RemotePath)) -replace "`r`n", "`n")
    return
}
if (-not (Test-Path $Dump)) { throw "No existe el dump: $Dump (corre antes actualizar_desde_prod.ps1)" }
$Dump = (Resolve-Path $Dump).Path

# ---- 1. Validar dump local ----
$info = Get-Item $Dump
$tail = (Get-Content $Dump -Tail 5) -join "`n"
if ($tail -notmatch 'Dump completed') {
    throw "El dump parece incompleto (no termina con '-- Dump completed'): $Dump"
}
Write-Host ("Dump: {0}  ({1:N1} MB, generado {2:dd/MM/yyyy HH:mm})" -f $Dump, ($info.Length / 1MB), $info.LastWriteTime)
if ($info.LastWriteTime -lt (Get-Date).AddDays(-1)) {
    Write-Warning 'El dump tiene mas de 1 dia: prod pudo recibir datos nuevos desde entonces.'
}

# ---- 2. Comprimir ----
$ts = Get-Date -Format 'yyyyMMdd_HHmmss'
$tmpDir = Join-Path ([IO.Path]::GetTempPath()) "themis_subida_$ts"
New-Item -ItemType Directory -Force $tmpDir | Out-Null
$gz = Join-Path $tmpDir 'dump.sql.gz'
Write-Host 'Comprimiendo...'
$in = [IO.File]::OpenRead($Dump)
try {
    $out = [IO.File]::Create($gz)
    try {
        $z = New-Object IO.Compression.GZipStream($out, [IO.Compression.CompressionLevel]::Optimal)
        try { $in.CopyTo($z) } finally { $z.Dispose() }
    } finally { $out.Dispose() }
} finally { $in.Dispose() }
Write-Host ("  {0:N1} MB comprimido" -f ((Get-Item $gz).Length / 1MB))

# ---- script que se ejecuta en el servidor (bash, LF) ----
$remoteScript = @'
#!/usr/bin/env bash
# Uso: importar_dump.sh <ruta_proyecto> <dump.sql.gz> <timestamp>
set -euo pipefail
APP="$1"; DUMP="$2"; TS="$3"
ENVF="$APP/.env"
[ -f "$ENVF" ] || { echo "No existe $ENVF"; exit 1; }

envval() {
    grep -E "^$1=" "$ENVF" | tail -1 | cut -d= -f2- \
        | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'\$//" -e 's/\r$//'
}
DB_HOST="$(envval DB_HOST)"; DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="$(envval DB_PORT)"; DB_PORT="${DB_PORT:-3306}"
DB_NAME="$(envval DB_DATABASE)"
DB_USER="$(envval DB_USERNAME)"
export MYSQL_PWD="$(envval DB_PASSWORD)"
[ -n "$DB_NAME" ] && [ -n "$DB_USER" ] || { echo "Faltan DB_DATABASE/DB_USERNAME en $ENVF"; exit 1; }

MY=(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" --default-character-set=utf8mb4)
MYDUMP=(mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" --default-character-set=utf8mb4 --single-transaction --routines --triggers --no-tablespaces)

command -v mysql >/dev/null && command -v mysqldump >/dev/null || { echo "Faltan mysql/mysqldump en el servidor"; exit 1; }
"${MY[@]}" -e "SELECT 1" "$DB_NAME" >/dev/null

echo "[servidor] a) Respaldando '$DB_NAME'..."
BKDIR="$HOME/backups/themis"
mkdir -p "$BKDIR"; chmod 700 "$BKDIR"
BK="$BKDIR/prod_${DB_NAME}_${TS}.sql.gz"
"${MYDUMP[@]}" "$DB_NAME" | gzip > "$BK"
gzip -t "$BK"
zcat "$BK" | tail -n 5 | grep -q "Dump completed" || { echo "El respaldo no termina con 'Dump completed' (incompleto), aborto sin tocar la base."; exit 1; }
chmod 600 "$BK"
echo "[servidor]    respaldo OK: $BK ($(du -h "$BK" | cut -f1))"

vaciar_base() {
    {
        echo "SET FOREIGN_KEY_CHECKS=0;"
        "${MY[@]}" -N -B -e "SELECT CONCAT('DROP ', IF(table_type='VIEW','VIEW','TABLE'), ' IF EXISTS \`', table_name, '\`;') FROM information_schema.tables WHERE table_schema='$DB_NAME'"
        echo "SET FOREIGN_KEY_CHECKS=1;"
    } | "${MY[@]}" "$DB_NAME"
}
# El dump trae (o puede traer) DROP/CREATE DATABASE y USE: se sacan para importar en la base de prod.
importar() {
    zcat "$1" | grep -vE '^(DROP DATABASE |CREATE DATABASE |USE )' | "${MY[@]}" "$DB_NAME"
}

echo "[servidor] b) Vaciando e importando..."
if ! { vaciar_base && importar "$DUMP"; }; then
    echo
    echo "!!! FALLO el import. Restaurando automaticamente el respaldo previo..."
    if vaciar_base && importar "$BK"; then
        echo "!!! Respaldo restaurado: la base de prod volvio al estado anterior ($BK)."
    else
        echo "!!! FALLO TAMBIEN LA RESTAURACION AUTOMATICA. La base esta INCOMPLETA."
        echo "!!! Restaurar a mano con el respaldo: $BK"
        echo "!!!   (vaciar las tablas y: zcat $BK | mysql -u$DB_USER -p $DB_NAME)"
    fi
    rm -f "$DUMP"
    exit 1
fi

echo "[servidor] c) Control:"
"${MY[@]}" -N -B "$DB_NAME" -e "SELECT CONCAT('  tablas=', COUNT(*)) FROM information_schema.tables WHERE table_schema='$DB_NAME'"
for t in users clientes migrations; do
    "${MY[@]}" -N -B "$DB_NAME" -e "SELECT CONCAT('  $t=', COUNT(*)) FROM \`$t\`" 2>/dev/null || echo "  $t=(no existe)"
done
rm -f "$DUMP"
echo "[servidor] listo."
'@
$remoteScript = $remoteScript -replace "`r`n", "`n"
$shLocal = Join-Path $tmpDir 'importar_dump.sh'
[IO.File]::WriteAllText($shLocal, $remoteScript, (New-Object Text.UTF8Encoding($false)))

if ($DryRun) {
    Write-Host ''
    Write-Host "DryRun: no se conecto a $Target. Se hubiera hecho:" -ForegroundColor Yellow
    Write-Host "  - leer DB_DATABASE de $RemotePath/.env y pedir confirmacion"
    Write-Host "  - scp dump.sql.gz + importar_dump.sh a /tmp"
    Write-Host "  - respaldar prod en ~/backups/themis/ (servidor), vaciar la base e importar"
    Remove-Item $tmpDir -Recurse -Force -ErrorAction SilentlyContinue
    return
}

try {
    # ---- 3. Confirmacion ----
    Write-Host "Conectando a $Target ..."
    $dbName = ((Invoke-Remote "grep -E '^DB_DATABASE=' '$RemotePath/.env' | tail -1 | cut -d= -f2- | tr -d '\042\r'") | Out-String).Trim()
    if ($LASTEXITCODE -ne 0 -or -not $dbName) { throw "No se pudo leer DB_DATABASE de $RemotePath/.env en $Target" }

    Write-Host ''
    Write-Host "SE VA A REEMPLAZAR LA BASE DE PRODUCCION '$dbName' en $Target" -ForegroundColor Red
    Write-Host "con el dump local del $($info.LastWriteTime.ToString('dd/MM/yyyy HH:mm')). Se hace un respaldo antes, en el servidor."
    $r = if ($PSBoundParameters.ContainsKey('Confirmar')) { Write-Host "Confirmacion recibida por parametro: '$Confirmar'"; $Confirmar } else { Read-Host "Escribi el nombre de la base ($dbName) para continuar" }
    if ($r -ne $dbName) { Write-Host 'Cancelado.'; return }

    # ---- 4. Subir ----
    $remoteGz = "/tmp/themis_dump_$ts.sql.gz"
    $remoteSh = "/tmp/themis_importar_$ts.sh"
    Write-Host 'Subiendo...'
    Copy-Remote $gz $remoteGz
    if ($LASTEXITCODE -ne 0) { throw 'Fallo scp del dump' }
    Copy-Remote $shLocal $remoteSh
    if ($LASTEXITCODE -ne 0) { throw 'Fallo scp del script' }
    Invoke-Remote "chmod 600 '$remoteGz'; chmod 700 '$remoteSh'"

    # ---- 5. Ejecutar en el servidor ----
    Invoke-Remote "bash '$remoteSh' '$RemotePath' '$remoteGz' '$ts'; rc=`$?; rm -f '$remoteSh'; exit `$rc"
    if ($LASTEXITCODE -ne 0) { throw 'El import en el servidor fallo (ver mensajes arriba; el respaldo esta en ~/backups/themis del servidor).' }

    Write-Host ''
    Write-Host "Listo: la base '$dbName' de produccion fue reemplazada." -ForegroundColor Green
    Write-Host "Respaldo previo (en el servidor): ~/backups/themis/prod_${dbName}_$ts.sql.gz"
} finally {
    Remove-Item $tmpDir -Recurse -Force -ErrorAction SilentlyContinue
}
