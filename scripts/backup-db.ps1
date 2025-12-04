param(
    [string]$DbName = 'system_e',
    [string]$DbUser = 'app_user'
)

$timestamp = Get-Date -Format yyyyMMddHHmmss
$out = "${DbName}_backup_$timestamp.sql"
Write-Output "Backing up $DbName to $out"
mysqldump -u $DbUser -p --databases $DbName > $out
Write-Output "Backup finished: $out"
