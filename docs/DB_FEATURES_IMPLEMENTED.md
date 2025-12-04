# Database Features Implemented (System - E)

This file summarizes the database and data-related features implemented to satisfy the checklist in `System - E`:

- Normalization: Separate tables are used for `families`, `evacuees`, `evacuation_areas`, `disaster_predictions`, and more.
- Triggers: MySQL triggers maintain `evacuation_areas.current_occupancy` for families inserted/updated/deleted (`2025_12_02_000003_add_family_occupancy_triggers.php`).
- Transactions: Important multi-table write operations use DB transactions (e.g., `FamilyController` store/update/check-out flows).
- Audit Logs: `audit_logs` table, `AuditLog` model, and `AuditObserver` added to log create/update/delete on important models.
Login History: Not tracked in this repo. Authentication and login history are handled by `System-E_Admin` in the shared `userevac_db`.
- Referential Integrity: `constrained()` + onDelete rules used in migrations for robust FK behavior.
- Optimization: Indexes for `families.status`, `families.evacuated_at`, and `evacuees.family_id` added (`2025_12_02_000004_add_indexes_to_families_and_others.php`).
- Encryption: Laravel encryption and `cipher` configured; add 'encrypted' casts where appropriate (see `User` model in admin repo).
- Domain Constraints: Enums used for `status` columns and server-side validation used for domain checks.
- Limit DB user privileges: `docs/db/privileges.sql` includes a sample `CREATE USER` statements for least privilege.
- Recovery & Backup: Added `docs/DB_BACKUP_AND_RECOVERY.md` with backup steps and restore verification.

How to verify:
1) `System - E` does not handle login. To verify login history, run `php artisan migrate` in the `System-E_Admin` project and log in there; view the `login_histories` table in the shared `userevac_db`.
2) Insert a `family` with `status=evacuated` into an evacuation area and find the `current_occupancy` updated.
3) Update a `family` total_members, inspect `audit_logs` and `evacuation_areas` occupancy.
4) Review the index usage via `EXPLAIN` for queries selecting by `status` or `evacuated_at`.
