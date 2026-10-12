-- Tripwire for config/config.php SITE_DOMAIN reverts.
-- Logs every content change with timestamp + hash to storage/logs/config-watch.log.
-- Run hidden in background: powershell -WindowStyle Hidden -File tools/config-watch.ps1
$ErrorActionPreference = 'SilentlyContinue'
$root  = Split-Path -Parent $PSScriptRoot
$file  = Join-Path $root 'config\config.php'
$log   = Join-Path $root 'storage\logs\config-watch.log'
function HashOf($p) {
  try { (Get-FileHash -LiteralPath $p -Algorithm SHA256).Hash } catch { 'UNREADABLE' }
}
$last = HashOf $file
Add-Content -LiteralPath $log -Value ("[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] watch started, sha=$($last.Substring(0,12))")
while ($true) {
  Start-Sleep -Seconds 10
  $now = HashOf $file
  if ($now -ne $last) {
    $line = try { (Select-String -LiteralPath $file -Pattern "SITE_DOMAIN.*getenv" | Select-Object -First 1).Line.Trim() } catch { '?' }
    Add-Content -LiteralPath $log -Value ("[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] CHANGED sha=$($now.Substring(0,12)) :: $line")
    $last = $now
  }
}
