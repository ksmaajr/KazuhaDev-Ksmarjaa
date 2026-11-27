param([int]$WebPort = 8000)
$ErrorActionPreference = 'Stop'
$projectPath = $PSScriptRoot
$localPath = Join-Path $projectPath '.local'
$tempPath = Join-Path $localPath 'php-tmp'
New-Item -ItemType Directory -Path $tempPath -Force | Out-Null
function Test-LocalPort([int]$Port) {
    $client = [System.Net.Sockets.TcpClient]::new()
    try { $client.Connect('127.0.0.1', $Port); return $true } catch { return $false } finally { $client.Dispose() }
}
if (-not (Test-LocalPort 3307)) {
    $mysqlPath = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqld.exe'
    $mysqlData = Join-Path $localPath 'mysql'
    if (-not (Test-Path -LiteralPath $mysqlPath)) { throw 'MySQL lokal tidak ditemukan. Jalankan MySQL Anda dan sesuaikan .env.' }
    if (-not (Test-Path -LiteralPath (Join-Path $mysqlData 'auto.cnf'))) { throw 'Data MySQL lokal belum tersedia. Lihat README.md untuk instalasi di mesin baru.' }
    $arguments = @('--no-defaults', '--basedir="C:\Program Files\MySQL\MySQL Server 8.0"', ('--datadir="' + $mysqlData + '"'), '--port=3307', '--bind-address=127.0.0.1', '--mysqlx=0', ('--log-error="' + (Join-Path $localPath 'mysql.log') + '"'), ('--pid-file="' + (Join-Path $localPath 'mysql.pid') + '"'))
    $databaseProcess = Start-Process -FilePath $mysqlPath -ArgumentList $arguments -WindowStyle Hidden -PassThru
    for ($attempt=0; $attempt -lt 40 -and -not (Test-LocalPort 3307); $attempt++) { Start-Sleep -Milliseconds 250 }
    if (-not (Test-LocalPort 3307)) { throw 'MySQL gagal berjalan. Periksa .local/mysql.log.' }
}
if (-not (Test-LocalPort $WebPort)) {
    $phpPath = (Get-Command php.exe -ErrorAction Stop).Source
    $publicPath = Join-Path $projectPath 'public'
    $routerPath = Join-Path $projectPath 'vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php'
    $arguments = @('-d', ('sys_temp_dir="' + $tempPath + '"'), '-d', ('upload_tmp_dir="' + $tempPath + '"'), '-S', ('127.0.0.1:' + $WebPort), '-t', ('"' + $publicPath + '"'), ('"' + $routerPath + '"'))
    $webProcess = Start-Process -FilePath $phpPath -ArgumentList $arguments -WorkingDirectory $publicPath -WindowStyle Hidden -RedirectStandardOutput (Join-Path $localPath 'server.out.log') -RedirectStandardError (Join-Path $localPath 'server.err.log') -PassThru
    $webProcess.Id | Set-Content (Join-Path $localPath 'web-process-id')
    for ($attempt=0; $attempt -lt 30 -and -not (Test-LocalPort $WebPort); $attempt++) { Start-Sleep -Milliseconds 200 }
}
$url = 'http://127.0.0.1:' + $WebPort
$response = Invoke-WebRequest ($url + '/login') -UseBasicParsing
if ($response.StatusCode -ne 200 -or $response.Content -notmatch 'KazuhaDev') { throw 'Port web sedang digunakan aplikasi lain atau server belum siap.' }
$schedulerPidPath = Join-Path $localPath 'scheduler-process-id'
$schedulerRunning = $false
if (Test-Path -LiteralPath $schedulerPidPath) {
    $schedulerProcessId = Get-Content -LiteralPath $schedulerPidPath -ErrorAction SilentlyContinue
    if ($schedulerProcessId -match '^\d+$') {
        $schedulerProcess = Get-CimInstance Win32_Process -Filter "ProcessId = $schedulerProcessId" -ErrorAction SilentlyContinue
        $schedulerRunning = $schedulerProcess -and $schedulerProcess.CommandLine -like ('*' + $projectPath + '*artisan*schedule:work*')
    }
}
if (-not $schedulerRunning) {
    $phpPath = (Get-Command php.exe -ErrorAction Stop).Source
    $schedulerArguments = @('-d', ('sys_temp_dir="' + $tempPath + '"'), ('"' + (Join-Path $projectPath 'artisan') + '"'), 'schedule:work', '--no-interaction')
    $schedulerProcess = Start-Process -FilePath $phpPath -ArgumentList $schedulerArguments -WorkingDirectory $projectPath -WindowStyle Hidden -RedirectStandardOutput (Join-Path $localPath 'scheduler.out.log') -RedirectStandardError (Join-Path $localPath 'scheduler.err.log') -PassThru
    $schedulerProcess.Id | Set-Content -LiteralPath $schedulerPidPath
}
Write-Host ('KazuhaDev. berjalan: ' + $url + '/login')
Write-Host ('Member portal: ' + $url + '/member/login')
Write-Host ('API console: ' + $url + '/api-console')
Write-Host 'MySQL: 127.0.0.1:3307 / keyforge'
Write-Host 'Pembersihan invoice aktif: setiap menit, hanya invoice belum diproses gateway yang berusia minimal 24 jam.'
Write-Host 'Kredensial lokal: .local/access.txt'
