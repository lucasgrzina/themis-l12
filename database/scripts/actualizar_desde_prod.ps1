<#
.SYNOPSIS
  Restaura un dump de produccion en la base local y le aplica la estructura de Themis L12.

.EXAMPLE
  .\database\scripts\actualizar_desde_prod.ps1 -Dump C:\descargas\prod_2026-10-05.sql

.EXAMPLE
  # Igual que arriba, pero ademas siembra la parametrizacion/datos del informe de liquidacion:
  .\database\scripts\actualizar_desde_prod.ps1 -Dump C:\descargas\prod_2026-10-05.sql -SeedLiquidacion

  ATENCION: BORRA y recrea la base indicada (por defecto `themis`) antes de importar.
  Luego corre `php artisan migrate` (migraciones posteriores al script SQL) y al final
  exporta un dump de la base resultante SIEMPRE a public\adminer.sql (se sobrescribe).
  Ese archivo NO va a git (esta en .gitignore): se lleva a prod con
  .\database\scripts\subir_a_prod.ps1 (respalda prod, sube por SSH e importa).
  IMPORTANTE: tiene datos reales; public\.htaccess bloquea su descarga por HTTP.
  El seeder del informe de liquidacion del Time
  (`php artisan db:seed --class=AgostoLiquidacionSeeder`) es OPCIONAL: solo se corre
  si se pasa el switch -SeedLiquidacion.
#>
param(
    [Parameter(Mandatory = $true)][string]$Dump,
    [string]$Database = 'themis',
    [string]$MysqlBin = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe',
    [string]$User = 'root',
    [switch]$Yes,
    [switch]$SeedLiquidacion
)

$ErrorActionPreference = 'Stop'
$mysqldump = Join-Path (Split-Path $MysqlBin) 'mysqldump.exe'
$publicDir = Join-Path $PSScriptRoot '..\..\public'
$estructura = Join-Path $PSScriptRoot 'aplicar_estructura_l12_a_themis.sql'

if (-not (Test-Path $Dump))       { throw "No existe el dump: $Dump" }
if (-not (Test-Path $MysqlBin))   { throw "No existe mysql.exe: $MysqlBin" }
if (-not (Test-Path $mysqldump))  { throw "No existe mysqldump.exe: $mysqldump" }
if (-not (Test-Path $estructura)) { throw "No existe $estructura" }

if (-not $Yes) {
    $r = Read-Host "Se va a BORRAR la base '$Database' y restaurar '$Dump'. Escribi SI para continuar"
    if ($r -ne 'SI') { Write-Host 'Cancelado.'; exit 1 }
}

$mysql = "`"$MysqlBin`" -u$User --default-character-set=utf8mb4"

Write-Host "1/5 Recreando base '$Database'..."
cmd /c "$mysql -e `"DROP DATABASE IF EXISTS ``$Database``; CREATE DATABASE ``$Database`` CHARACTER SET utf8mb4`""
if ($LASTEXITCODE -ne 0) { throw 'Fallo al recrear la base' }

Write-Host '2/5 Importando dump (puede tardar)...'
# El dump (Adminer) trae DROP/CREATE DATABASE y USE <otra_base>: se sacan para
# que se importe en $Database y no pise la base original del dump.
$dumpLimpio = Join-Path ([IO.Path]::GetTempPath()) ("themis_dump_{0}.sql" -f [guid]::NewGuid().ToString('N'))
$utf8 = New-Object System.Text.UTF8Encoding($false)
$w = New-Object System.IO.StreamWriter($dumpLimpio, $false, $utf8)
try {
    foreach ($linea in [IO.File]::ReadLines((Resolve-Path $Dump).Path, $utf8)) {
        if ($linea -match '^(DROP DATABASE |CREATE DATABASE |USE )') { continue }
        $w.WriteLine($linea)
    }
} finally { $w.Dispose() }
try {
    cmd /c "$mysql $Database < `"$dumpLimpio`""
    if ($LASTEXITCODE -ne 0) { throw 'Fallo al importar el dump' }
} finally { Remove-Item $dumpLimpio -Force -ErrorAction SilentlyContinue }

Write-Host '3/5 Aplicando estructura L12...'
cmd /c "$mysql $Database < `"$estructura`""
if ($LASTEXITCODE -ne 0) { throw 'Fallo al aplicar la estructura' }

Write-Host '4/6 Corriendo migraciones pendientes (timer, carpeta destruida, permiso tramites:DC, liquidacion Time, etc.)...'
Push-Location (Join-Path $PSScriptRoot '..\..')
try {
    php artisan migrate --force
    if ($LASTEXITCODE -ne 0) { throw 'Fallo php artisan migrate' }
} finally { Pop-Location }

if ($SeedLiquidacion) {
    Write-Host '5/6 Sembrando parametrizacion del informe de liquidacion (tarifas, participaciones, TC, presupuestos, Director, horas SAVARY)...'
    Push-Location (Join-Path $PSScriptRoot '..\..')
    try {
        php artisan db:seed --class=AgostoLiquidacionSeeder --force
        if ($LASTEXITCODE -ne 0) { throw 'Fallo php artisan db:seed AgostoLiquidacionSeeder' }
    } finally { Pop-Location }
} else {
    Write-Host '5/6 Seeder de liquidacion OMITIDO (pasa -SeedLiquidacion para incluirlo).'
}

Write-Host '6/6 Exportando dump a public\adminer.sql...'
New-Item -ItemType Directory -Force $publicDir | Out-Null
$salida = Join-Path (Resolve-Path $publicDir) 'adminer.sql'
cmd /c "`"$mysqldump`" -u$User --default-character-set=utf8mb4 --single-transaction --routines --triggers $Database > `"$salida`""
if ($LASTEXITCODE -ne 0) { throw 'Fallo al exportar el dump' }

Write-Host "Listo: '$Database' tiene los datos de prod + estructura L12."
Write-Host "Dump generado: $salida"
