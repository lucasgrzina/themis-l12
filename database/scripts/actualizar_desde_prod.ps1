<#
.SYNOPSIS
  Restaura un dump de produccion en la base local y le aplica la estructura de Themis L12.

.EXAMPLE
  .\database\scripts\actualizar_desde_prod.ps1 -Dump C:\descargas\prod_2026-10-05.sql

  ATENCION: BORRA y recrea la base indicada (por defecto `themis`) antes de importar.
  Al final exporta un dump de la base resultante a database\bkp\.
#>
param(
    [Parameter(Mandatory = $true)][string]$Dump,
    [string]$Database = 'themis',
    [string]$MysqlBin = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe',
    [string]$User = 'root',
    [switch]$Yes
)

$ErrorActionPreference = 'Stop'
$mysqldump = Join-Path (Split-Path $MysqlBin) 'mysqldump.exe'
$bkpDir = Join-Path $PSScriptRoot '..\bkp'
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

Write-Host "1/4 Recreando base '$Database'..."
cmd /c "$mysql -e `"DROP DATABASE IF EXISTS ``$Database``; CREATE DATABASE ``$Database`` CHARACTER SET utf8mb4`""
if ($LASTEXITCODE -ne 0) { throw 'Fallo al recrear la base' }

Write-Host '2/4 Importando dump (puede tardar)...'
cmd /c "$mysql $Database < `"$Dump`""
if ($LASTEXITCODE -ne 0) { throw 'Fallo al importar el dump' }

Write-Host '3/4 Aplicando estructura L12...'
cmd /c "$mysql $Database < `"$estructura`""
if ($LASTEXITCODE -ne 0) { throw 'Fallo al aplicar la estructura' }

Write-Host '4/4 Exportando dump a database\bkp...'
New-Item -ItemType Directory -Force $bkpDir | Out-Null
$salida = Join-Path (Resolve-Path $bkpDir) ("{0}_l12_{1:yyyyMMdd_HHmm}.sql" -f $Database, (Get-Date))
cmd /c "`"$mysqldump`" -u$User --default-character-set=utf8mb4 --single-transaction --routines --triggers $Database > `"$salida`""
if ($LASTEXITCODE -ne 0) { throw 'Fallo al exportar el dump' }

Write-Host "Listo: '$Database' tiene los datos de prod + estructura L12."
Write-Host "Dump generado: $salida"
