<#
.SYNOPSIS
  Sincroniza public\uploads\docs (archivos de la solapa Docs) hacia el servidor de PRODUCCION por SSH.
  Solo AGREGA / reemplaza archivos: nunca borra nada en el servidor.

.DESCRIPTION
  - Repara los nombres de archivo dañados por una descarga con el juego de caracteres equivocado
    (UTF-8 leido como Windows-1252: "PENSIÃ“N" -> "PENSIÓN", y la version de 2 capas
    "VIÃƒâ€˜AS" -> "VIÑAS"). El servidor recibe el nombre correcto, que es el que guarda la base.
    Los archivos locales NO se renombran: se envian a traves de hardlinks temporales (sin copiar datos).
  - Compara por (nombre destino, tamanio) contra lo que ya esta en el servidor y envia solo lo que
    falta o difiere. Se puede cortar y volver a correr: continua donde quedo.
  - Transmite por lotes (por defecto ~1 GB) con `tar` por la tuberia SSH, sin archivo temporal
    en el servidor (/tmp de prod es RAM de 7,8 GB y no entra un archivo de 12 GB).
  - En el servidor extrae como www-data (umask 002: archivos 664 / carpetas 775), conserva fechas.
  - Reintenta cada lote hasta 3 veces. Al final vuelve a comparar, reporta lo que falte y cruza
    contra la base: documentos de la BD sin archivo en el servidor.
  - Solo sincroniza la subcarpeta docs (tmp es temporal y no se sube).

.EXAMPLE
  .\database\scripts\subir_uploads_a_prod.ps1 -DryRun     # solo calcula que falta, no envia nada

.EXAMPLE
  .\database\scripts\subir_uploads_a_prod.ps1             # sincroniza

.EXAMPLE
  # prueba sin tocar uploads de prod: 5 archivos a una carpeta de prueba del servidor
  .\database\scripts\subir_uploads_a_prod.ps1 -MaxFiles 5 -RemoteDest /tmp/zz_uploads_test -SkipDbCheck
#>
param(
    [string]$Target = 'ubuntu@168.205.94.185',
    [string]$RemotePath = '/var/www/html/themis',
    [string]$Key = 'C:\AmazonKeys\baehost.ppk',
    [string]$Source = (Join-Path $PSScriptRoot '..\..\public\uploads'),
    # Carpeta de destino en el servidor (por defecto public/uploads del proyecto).
    [string]$RemoteDest = '',
    # Carpeta para los hardlinks temporales: debe estar en el MISMO volumen que $Source.
    [string]$StageDir = ([IO.Path]::GetTempPath()),
    [int]$MaxBatchMB = 1024,
    [int]$MaxBatchFiles = 2000,
    [int]$MaxFiles = 0,
    # Expresion regular: solo envia los archivos pendientes cuyo nombre destino coincida (para pruebas).
    [string]$Filter = '',
    [switch]$DryRun,
    [switch]$SkipDbCheck
)

$ErrorActionPreference = 'Stop'
if (-not $RemoteDest) { $RemoteDest = "$RemotePath/public/uploads" }
try { [Console]::OutputEncoding = [Text.Encoding]::UTF8 } catch { }

$usePutty = [bool]($Key -and $Key -like '*.ppk')
$tool = if ($usePutty) { 'plink' } else { 'ssh' }
if (-not (Get-Command $tool -ErrorAction SilentlyContinue)) { throw "No se encontro '$tool' en el PATH." }
$sshExe = (Get-Command $tool).Source
if ($Key -and -not (Test-Path $Key)) { throw "No existe la clave: $Key" }
$tarExe = Join-Path $env:SystemRoot 'System32\tar.exe'   # bsdtar de Windows
if (-not (Test-Path $tarExe)) { throw "No se encontro $tarExe" }
if (-not (Test-Path (Join-Path $Source 'docs'))) { throw "No existe $Source\docs" }
$Source = (Resolve-Path $Source).Path.TrimEnd('\')
$docsLocal = Join-Path $Source 'docs'

# Los comandos remotos usan solo comillas simples (compatible con Windows PowerShell 5.1).
function Invoke-Remote([string]$cmd) {
    if ($usePutty) { & plink -batch -i $Key $Target $cmd }
    else {
        $o = @('-o', 'ConnectTimeout=15'); if ($Key) { $o += @('-i', $Key) }
        & ssh @o $Target $cmd
    }
}
function Get-RemoteFiles {
    # devuelve hashtable docs/<nombre> -> tamanio
    $h = @{}
    $out = Invoke-Remote "test -d '$RemoteDest/docs' && cd '$RemoteDest' && find docs -type f -printf '%p\t%s\n'"
    foreach ($l in $out) {
        $p = ([string]$l).Split("`t")
        if ($p.Count -eq 2) { $h[$p[0]] = [int64]$p[1] }
    }
    return $h
}
function Format-Size([double]$b) {
    if ($b -ge 1GB) { return ('{0:N2} GB' -f ($b / 1GB)) }
    if ($b -ge 1MB) { return ('{0:N1} MB' -f ($b / 1MB)) }
    return ('{0:N0} KB' -f ($b / 1KB))
}

# ---- reparacion de nombres (UTF-8 leido como Windows-1252, 1 o 2 capas) ----
$enc1252 = [Text.Encoding]::GetEncoding(1252, (New-Object Text.EncoderExceptionFallback), (New-Object Text.DecoderExceptionFallback))
$utf8Strict = New-Object Text.UTF8Encoding($false, $true)
function Repair-FileName([string]$name) {
    $cur = $name
    for ($i = 0; $i -lt 4; $i++) {
        if ($cur -notmatch '[^\x00-\x7F]') { break }
        try { $nx = $utf8Strict.GetString($enc1252.GetBytes($cur)) } catch { break }   # no es mojibake reparable
        if ($nx -eq $cur -or $nx.Contains([string][char]0xFFFD)) { break }
        $cur = $nx
    }
    return $cur
}

Add-Type -Namespace Win32 -Name Native -MemberDefinition '[DllImport("kernel32.dll", CharSet=CharSet.Unicode, SetLastError=true)] public static extern bool CreateHardLink(string lpFileName, string lpExistingFileName, IntPtr lpSecurityAttributes);'

# ---- 1. Inventario ----
Write-Host "Local : $docsLocal"
Write-Host "Remoto: $Target`:$RemoteDest/docs"
Write-Host 'Leyendo archivos locales...'
$local = @{}      # docs/<nombre destino> -> tamanio
$srcName = @{}    # docs/<nombre destino> -> nombre real en disco
$reparados = 0; $colisiones = New-Object System.Collections.ArrayList
Get-ChildItem -LiteralPath $docsLocal -File | ForEach-Object {
    $dn = Repair-FileName $_.Name
    if ($dn -ne $_.Name) { $reparados++ }
    $rk = "docs/$dn"
    if ($local.ContainsKey($rk)) { [void]$colisiones.Add($_.Name); return }
    $local[$rk] = $_.Length
    $srcName[$rk] = $_.Name
}
Write-Host ("  {0} archivos, {1}; {2} con nombre reparado" -f $local.Count, (Format-Size (($local.Values | Measure-Object -Sum).Sum)), $reparados)
if ($colisiones.Count -gt 0) {
    Write-Warning ("{0} archivo(s) quedarian con un nombre ya usado tras reparar; se omiten (ej: {1})" -f $colisiones.Count, $colisiones[0])
}
$subdirs = @(Get-ChildItem -LiteralPath $docsLocal -Directory)
if ($subdirs.Count -gt 0) { Write-Warning "docs tiene $($subdirs.Count) subcarpeta(s): no se sincronizan (la aplicacion solo usa archivos sueltos)." }

Write-Host 'Leyendo archivos del servidor...'
$remote = Get-RemoteFiles
Write-Host ("  {0} archivos en el servidor" -f $remote.Count)

$needs = @($local.Keys | Where-Object { -not $remote.ContainsKey($_) -or $remote[$_] -ne $local[$_] } | Sort-Object)
$alDia = $local.Count - $needs.Count
if ($Filter) { $needs = @($needs | Where-Object { $_ -match $Filter }) }
$soloRemoto = @($remote.Keys | Where-Object { -not $local.ContainsKey($_) }).Count
$pending = $needs
if ($MaxFiles -gt 0 -and $pending.Count -gt $MaxFiles) { $pending = $pending[0..($MaxFiles - 1)] }
$pendBytes = [int64]0; foreach ($p in $pending) { $pendBytes += $local[$p] }
Write-Host ''
Write-Host ("A enviar: {0} archivos ({1}). Ya al dia: {2}. Solo en el servidor (no se tocan): {3}." -f $pending.Count, (Format-Size $pendBytes), $alDia, $soloRemoto)

if ($DryRun) {
    Write-Host 'DryRun: no se envio nada.' -ForegroundColor Yellow
    $pending | Select-Object -First 5 | ForEach-Object { Write-Host "  $_" }
    if ($pending.Count -gt 5) { Write-Host '  ...' }
    return
}

# ---- 2. Envio por lotes ----
if ($pending.Count -gt 0) {
    $work = Join-Path $StageDir ("themis_uploads_" + [guid]::NewGuid().ToString('N'))
    New-Item -ItemType Directory -Force $work | Out-Null
    $remoteCmd = "sudo -n -u www-data bash -c 'umask 002; mkdir -p $RemoteDest/docs; tar -xf - --no-same-permissions -C $RemoteDest'"
    if ($usePutty) { $sshLine = "`"$sshExe`" -batch -i `"$Key`" $Target `"$remoteCmd`"" }
    else { $sshLine = "`"$sshExe`" -o ConnectTimeout=15 " + $(if ($Key) { "-i `"$Key`" " } else { '' }) + "$Target `"$remoteCmd`"" }

    # armar lotes
    $batches = New-Object System.Collections.ArrayList
    $cur = New-Object System.Collections.ArrayList; $curBytes = [int64]0
    foreach ($p in $pending) {
        if ($cur.Count -gt 0 -and ($curBytes + $local[$p] -gt [int64]$MaxBatchMB * 1MB -or $cur.Count -ge $MaxBatchFiles)) {
            [void]$batches.Add(@{ Files = $cur; Bytes = $curBytes }); $cur = New-Object System.Collections.ArrayList; $curBytes = [int64]0
        }
        [void]$cur.Add($p); $curBytes += $local[$p]
    }
    if ($cur.Count -gt 0) { [void]$batches.Add(@{ Files = $cur; Bytes = $curBytes }) }

    $sent = [int64]0; $t0 = Get-Date; $n = 0
    try {
        foreach ($b in $batches) {
            $n++
            # carpeta temporal con hardlinks de los archivos del lote, con el nombre destino correcto
            $stage = Join-Path $work "lote_$n"
            $stageDocs = Join-Path $stage 'docs'
            New-Item -ItemType Directory -Force $stageDocs | Out-Null
            foreach ($rk in $b.Files) {
                $linkPath = Join-Path $stageDocs $rk.Substring(5)
                $real = Join-Path $docsLocal $srcName[$rk]
                if (-not [Win32.Native]::CreateHardLink($linkPath, $real, [IntPtr]::Zero)) {
                    $err = [Runtime.InteropServices.Marshal]::GetLastWin32Error()
                    throw "No se pudo crear el hardlink para '$($srcName[$rk])' (error $err). El directorio temporal debe estar en el mismo volumen que ${Source}; usa -StageDir."
                }
            }
            $cmdFile = Join-Path $work "lote_$n.cmd"
            $content = "@echo off`r`n`"$tarExe`" -cf - -C `"$stage`" docs | $sshLine`r`nexit /b %ERRORLEVEL%`r`n"
            [IO.File]::WriteAllText($cmdFile, $content, [Text.Encoding]::GetEncoding(850))

            $ok = $false
            for ($try = 1; $try -le 3 -and -not $ok; $try++) {
                Write-Host ("Lote {0}/{1}: {2} archivos, {3}{4}" -f $n, $batches.Count, $b.Files.Count, (Format-Size $b.Bytes), $(if ($try -gt 1) { " (reintento $try)" } else { '' }))
                & cmd.exe /c "`"$cmdFile`""
                if ($LASTEXITCODE -eq 0) { $ok = $true } else { Write-Warning "El lote $n fallo (codigo $LASTEXITCODE)."; Start-Sleep -Seconds 5 }
            }
            Remove-Item $stage -Recurse -Force -ErrorAction SilentlyContinue   # solo quita los hardlinks
            if (-not $ok) { throw "El lote $n fallo 3 veces. Volve a correr el script: continua donde quedo." }
            $sent += $b.Bytes
            $el = ((Get-Date) - $t0).TotalSeconds
            $mbs = if ($el -gt 0) { ($sent / 1MB) / $el } else { 0 }
            $resta = if ($mbs -gt 0) { [TimeSpan]::FromSeconds(($pendBytes - $sent) / 1MB / $mbs) } else { [TimeSpan]::Zero }
            Write-Host ("  enviado {0} de {1} ({2:N1} MB/s, restan ~{3:hh\:mm\:ss})" -f (Format-Size $sent), (Format-Size $pendBytes), $mbs, $resta)
        }
    } finally { Remove-Item $work -Recurse -Force -ErrorAction SilentlyContinue }
}

# ---- 3. Verificacion ----
Write-Host ''
Write-Host 'Verificando contra el servidor...'
$remote = Get-RemoteFiles
$falta = @($local.Keys | Where-Object { -not $remote.ContainsKey($_) })
$difiere = @($local.Keys | Where-Object { $remote.ContainsKey($_) -and $remote[$_] -ne $local[$_] })
Write-Host ("  en el servidor: {0} archivos | faltan: {1} | tamanio distinto: {2}" -f $remote.Count, $falta.Count, $difiere.Count)
if ($falta.Count + $difiere.Count -gt 0) {
    ($falta + $difiere) | Select-Object -First 10 | ForEach-Object { Write-Host "  $_" }
    if ($MaxFiles -eq 0) { Write-Warning 'Quedaron archivos sin sincronizar: volve a correr el script.' }
}

if (-not $SkipDbCheck) {
    Write-Host 'Cruzando con documento_clientes (base de produccion)...'
    $sql = @'
APP='__APP__'
v() { grep -E "^$1=" $APP/.env | tail -1 | cut -d= -f2- | tr -d '\042\r'; }
export MYSQL_PWD="$(v DB_PASSWORD)"
mysql -h"$(v DB_HOST)" -u"$(v DB_USERNAME)" "$(v DB_DATABASE)" -N -B -e 'SELECT nombre_archivo FROM documento_clientes WHERE deleted_at IS NULL AND nombre_archivo IS NOT NULL'
'@
    $id = [guid]::NewGuid().ToString('N')
    $lf = Join-Path ([IO.Path]::GetTempPath()) "themis_db_$id.sh"
    [IO.File]::WriteAllText($lf, (($sql.Replace('__APP__', $RemotePath)) -replace "`r`n", "`n"), (New-Object Text.UTF8Encoding($false)))
    try {
        if ($usePutty) { & pscp -batch -q -i $Key $lf "${Target}:/tmp/themis_db_$id.sh" } else { $o = @(); if ($Key) { $o = @('-i', $Key) }; & scp @o $lf "${Target}:/tmp/themis_db_$id.sh" }
        $names = @(Invoke-Remote "bash '/tmp/themis_db_$id.sh'; rm -f '/tmp/themis_db_$id.sh'") | ForEach-Object { ([string]$_).Trim() } | Where-Object { $_ }
    } finally { Remove-Item $lf -Force -ErrorAction SilentlyContinue }
    $sinArchivo = @($names | Where-Object { -not $remote.ContainsKey('docs/' + $_) })
    Write-Host ("  documentos en la BD: {0} | sin archivo en el servidor: {1}" -f $names.Count, $sinArchivo.Count)
    if ($sinArchivo.Count -gt 0) {
        $sinArchivo | Select-Object -First 10 | ForEach-Object { Write-Host "  $_" }
        if ($sinArchivo.Count -gt 10) { Write-Host '  ...' }
    }
}
Write-Host ''
Write-Host 'Listo.' -ForegroundColor Green
