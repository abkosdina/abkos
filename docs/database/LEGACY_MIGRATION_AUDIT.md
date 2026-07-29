# Legacy Migration Audit

## Summary
A legacy advertisement workflow migration exists at `database/migrations/legacy/2024_01_01_000000_create_advertisement_workflow_tables.php`. This file defines historic advertisement workflow tables and appears to be a repository artifact rather than an active, current migration source.

## Legacy migration contents
The legacy migration creates the following tables:
- `advertisement_logs`
- `advertisement_workflow_audits`
- `advertisement_workflow_states`
- `advertisement_workflow_transitions`

It includes columns and indexes similar to earlier advertisement workflow schema designs, including:
- `advertisement_logs`: `advertisement_id`, `user_id`, `action`, `ip_address`, `user_agent`, `metadata`, plus indexes.
- `advertisement_workflow_audits`: `advertisement_id`, `uuid`, `old_state`, `new_state`, `user_id`, `role`, `action`, `reason`, `comment`, `ip_address`, `user_agent`, `metadata`, plus indexes.
- `advertisement_workflow_states`: `name`, `label`, `description`, boolean state flags, `meta`, plus indexes.
- `advertisement_workflow_transitions`: `from_state`, `to_state`, `action`, `label`, `description`, `required_roles`, `requires_reason`, `metadata`, plus indexes.

## Current status
- The legacy file is stored under `database/migrations/legacy`, which means it is not part of the standard migration chronology unless Laravel is explicitly configured to scan that folder.
- Active current migrations define advertisement workflow tables under `database/migrations/2026_07_13_000200_create_advertisement_workflow_and_audits.php` and related seed files.
- There is also a legacy code audit document at `LEGACY_WORKFLOW_ENGINE_AUDIT.md` describing the deprecated advertisement-specific workflow engine.

## Risk analysis
- If the legacy migration file is accidentally included in an active migration path, it may create duplicate or conflicting workflow tables.
- The live database metadata shows a mixture of workflow audit schema shapes, which may be the result of legacy migration artifacts or partial schema evolution.
- The legacy migration is a potential source of confusion for developers and may cause schema drift if retained near active migration files.

## Recommendations
1. Keep the legacy file as an archival artifact only.
   - Do not execute it in current deployments unless intentionally restoring an older schema shape.
   - If the legacy folder is being scanned by migrations, exclude it from standard migration runs.

2. Consider relocating the file outside `database/migrations`.
   - Move it to `docs/legacy/` or a repository archive folder to make its status explicit.

3. Update documentation and migration guidance.
   - Clearly state that `database/migrations/legacy/2024_01_01_000000_create_advertisement_workflow_tables.php` is legacy and not part of the active migration chain.
   - Reference `LEGACY_WORKFLOW_ENGINE_AUDIT.md` for the legacy advertisement-specific engine deprecation.

4. Verify migration status.
   - Run `php artisan migrate:status` and confirm that the legacy folder is not included in normal migration listings.
   - If needed, add a note to `README.md` or project setup documentation explaining the purpose of the `legacy/` folder.

## Conclusion
The legacy migration file is a historical artifact and should remain isolated from active database migrations. Its existence is useful for audit purposes, but it should not be part of the standard migration path. If no rollback or legacy restore is required, archive or remove it once the current generic workflow/approval schema is stable and validated.
