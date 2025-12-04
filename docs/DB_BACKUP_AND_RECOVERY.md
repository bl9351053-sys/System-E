# Backup & Recovery (System - E)

This document explains a simple demonstration plan for backing up and restoring the database used by the System - E project.

1) Quick mysqldump backup

```powershell
# Export DB
mysqldump -u app_user -p --databases system_e > system_e_backup_$(Get-Date -Format yyyyMMddHHmmss).sql
```

2) Restore

```powershell
# Import to the database (new or existing)
mysql -u app_user -p system_e < system_e_backup_20251202.sql
```

3) Automated (Windows Task Scheduler)

- Use the above mysqldump command in a PowerShell script, then schedule it to run nightly. Keep an artifact retention policy (e.g., keep last 14 backups).

4) Disaster plan outline

- Maintain a separate secondary machine/host for backups (or cloud storage for backup files).
- Regularly test restore to a dev environment.
- Keep database user credentials in a secrets manager and never commit them to source.

Notes
- Use incremental backups or a managed cloud backup service for production environments.
