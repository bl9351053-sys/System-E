# Verification Steps: DB Features (System - E)

Follow the steps below to verify the implemented features:

1) Migrate & Seed

```powershell
php artisan migrate --seed
```

2) Test Login History

- System - E has no login page; login activity is recorded by `System-E_Admin` into the shared `userevac_db`. To verify login history, log in via `System-E_Admin` and inspect `login_histories`.

3) Test Audit Logs

- Create/edit/destroy an important model (Family, EvacuationArea); check `audit_logs` for entries with `old_values` & `new_values`.

4) Test Triggers

- Create family with `status='evacuated'` and `evacuation_area_id` set; verify `evacuation_areas.current_occupancy` changed.

5) Test Transactions

- Ensure multi-table changes roll back when one of the operations fails by simulating an exception inside a transaction.
