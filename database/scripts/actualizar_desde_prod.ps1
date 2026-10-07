<#
.SYNOPSIS
  Restaura un dump de produccion en la base local y le aplica la estructura de Themis L12.

.EXAMPLE
  .\database\scripts\actualizar_desde_prod.ps1 -Dump C:\descargas\prod_2026-10-05.sql

  ATENCION: BORRA y recrea la base indicada (por defecto `themis`) antes de importar.
#>
param(
    [Parameter(Mandatory = $true)][string]$Dump,
    [string]$Database = 'themis',
    [string]$MysqlBin = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe',
    [string]$User = 'root',
    [switch]$Yes
)

$ErrorActionPreference = 'Stop'
$estructura = Join-Path $PSScriptRoot 'aplicar_estructura_l12_a_themis.sql'

if (-not (Test-Path $Dump))       { throw "No existe el dump: $Dump" }
if (-not (Test-Path $MysqlBin))   { throw "No existe mysql.exe: $MysqlBin" }
if (-not (Test-Path $estructura)) { throw "No existe $estructura" }

if (-not $Yes) {
    $r = Read-Host "Se va a BORRAR la base '$Database' y restaurar '$Dump'. Escribi SI para continuar"
    if ($r -ne 'SI') { Write-Host 'Cancelado.'; exit 1 }
}

$mysql = "`"$MysqlBin`" -u$User --default-character-set=utf8mb4"

Write-Host "1/3 Recreando base '$Database'..."
cmd /c "$mysql -e `"DROP DATABASE IF EXISTS ``$Database``; CREATE DATABASE ``$Database`` CHARACTER SET utf8mb4`""
if ($LASTEXITCODE -ne 0) { throw 'Fallo al recrear la base' }

Write-Host '2/3 Importando dump (puede tardar)...'
cmd /c "$mysql $Database < `"$Dump`""
if ($LASTEXITCODE -ne 0) { throw 'Fallo al importar el dump' }

Write-Host '3/3 Aplicando estructura L12...'
cmd /c "$mysql $Database < `"$estructura`""
if ($LASTEXITCODE -ne 0) { throw 'Fallo al aplicar la estructura' }

Write-Host "Listo: '$Database' tiene los datos de prod + estructura L12."
