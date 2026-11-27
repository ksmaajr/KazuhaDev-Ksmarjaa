$ErrorActionPreference = 'Stop'
Push-Location $PSScriptRoot
$names = @('DB_CONNECTION','DB_DATABASE','DB_HOST','DB_PORT','DB_USERNAME','DB_PASSWORD')
$previous = @{}
foreach ($name in $names) { $previous[$name] = [Environment]::GetEnvironmentVariable($name,'Process') }
try {
    $settings = @{}
    foreach ($line in Get-Content -LiteralPath '.env') {
        if ($line -match '^(DB_[A-Z_]+)=(.*)$') { $settings[$matches[1]] = $matches[2].Trim('"') }
    }
    $env:DB_CONNECTION='mysql'; $env:DB_DATABASE='keyforge_test'
    $env:DB_HOST=$settings['DB_HOST']; $env:DB_PORT=$settings['DB_PORT']
    $env:DB_USERNAME=$settings['DB_USERNAME']; $env:DB_PASSWORD=$settings['DB_PASSWORD']
    if ($env:DB_HOST -notin @('127.0.0.1','localhost') -or $env:DB_PORT -ne '3307') { throw 'Pengujian ini hanya untuk MySQL lokal khusus keyforge di port 3307.' }
    $tempPath = Join-Path $PSScriptRoot '.local\php-tmp'
    New-Item -ItemType Directory -Path $tempPath -Force | Out-Null
    php -d ('sys_temp_dir=' + $tempPath) vendor/bin/phpunit --testdox
    $testExit = $LASTEXITCODE
} finally {
    foreach ($name in $names) { if ($null -eq $previous[$name]) { Remove-Item -LiteralPath ('Env:' + $name) -ErrorAction SilentlyContinue } else { [Environment]::SetEnvironmentVariable($name,$previous[$name],'Process') } }
    Pop-Location
}
exit $testExit
