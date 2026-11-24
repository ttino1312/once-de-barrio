$ErrorActionPreference = 'Stop'

$project = $PSScriptRoot
$xampp = 'C:\xampp'
$vite = Join-Path $project 'node_modules\vite\bin\vite.js'
$vivo = Join-Path $project 'tiempo-real\servidor.js'
$sitioDestino = Join-Path $xampp 'htdocs\once-de-barrio-conectado'
$url = 'http://127.0.0.1/once-de-barrio-conectado/'

if (-not (Test-Path -LiteralPath $vite)) {
    throw 'Falta Vite en la carpeta nueva. Abrí una terminal ahí y ejecutá npm install.'
}

Start-Process -FilePath "$xampp\xampp-control.exe" -WorkingDirectory $xampp

if (-not (Get-Process -Name mysqld -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath "$xampp\mysql\bin\mysqld.exe" `
        -ArgumentList "--defaults-file=$xampp\mysql\bin\my.ini", '--standalone' `
        -WorkingDirectory $xampp -WindowStyle Hidden
}

if (-not (Get-Process -Name httpd -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath "$xampp\apache\bin\httpd.exe" `
        -WorkingDirectory $xampp -WindowStyle Hidden
}

Start-Process -FilePath 'explorer.exe' -ArgumentList "`"$project`""

$mysqlReady = $false
for ($attempt = 0; $attempt -lt 20; $attempt++) {
    try {
        & "$xampp\mysql\bin\mysql.exe" -u root -e 'SELECT 1' 2>$null | Out-Null
        if ($LASTEXITCODE -eq 0) {
            $mysqlReady = $true
            break
        }
    } catch {}
    Start-Sleep -Milliseconds 500
}

if (-not $mysqlReady) {
    throw 'MySQL no arrancó. Revisá el botón MySQL en la ventana de XAMPP.'
}

# Se compila la copia nueva y se publica en otra carpeta de Apache.
Push-Location $project
try {
    & npm.cmd run build
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo compilar el proyecto.' }
} finally {
    Pop-Location
}
if (-not (Test-Path -LiteralPath $sitioDestino)) {
    New-Item -ItemType Directory -Path $sitioDestino | Out-Null
}
Copy-Item -Path (Join-Path $project 'dist\*') -Destination $sitioDestino -Recurse -Force
$apiDestino = Join-Path $sitioDestino 'api'
if (-not (Test-Path -LiteralPath $apiDestino)) {
    New-Item -ItemType Directory -Path $apiDestino | Out-Null
}
Copy-Item -Path (Join-Path $project 'api\*') -Destination $apiDestino -Recurse -Force

$node = (Get-Command node.exe -ErrorAction Stop).Source
if (-not (Get-NetTCPConnection -LocalPort 4100 -State Listen -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath $node -ArgumentList "`"$vivo`"" -WorkingDirectory $project -WindowStyle Hidden
}

$ready = $false
for ($attempt = 0; $attempt -lt 30; $attempt++) {
    try {
        $response = Invoke-WebRequest -Uri $url -TimeoutSec 2 -UseBasicParsing
        if ($response.StatusCode -eq 200 -and $response.Content -match 'Once de Barrio') {
            $ready = $true
            break
        }
    } catch {
        Start-Sleep -Milliseconds 500
    }
}

if (-not $ready) {
    throw 'No se pudo abrir la nueva versión en Apache.'
}

Start-Process $url
