# Database Schema Audit

## Overview
This audit consolidates the current live database schema metadata from `storage/schema_tables.json`, `storage/schema_columns.json`, `storage/schema_indexes.json`, and `storage/schema_fks.json` with a review of active migration files in `database/migrations` and `Modules/Workflow/Database/Migrations`.

## Scope
- Advertisement domain tables
- Advertisement workflow state/transition/audit tables
- Generic workflow engine tables
- Approval engine tables
- Foreign keys, indexes, and schema consistency
- Legacy migration artifacts and schema drift

## Key Findings

### 1. Advertisement domain schema
- `advertisements` exists as a central domain table.
- `loan_offers` is present and correctly references `advertisements` via `advertisement_id` with `cascadeOnDelete()`.
- `advertisement_favorites.user_id` is foreign keyed to `users` and has an index.
- `advertisement_views.user_id` is foreign keyed to `users`, but `advertisement_views.advertisement_id` is only indexed and not defined as a foreign key in the migration metadata. This is a gap in referential integrity.
- Several advertisement support tables are present in the live metadata and active migrations, including `advertisement_contacts`, `advertisement_documents`, `advertisement_images`, `advertisement_logs`, and `advertisement_views`.

### 2. Advertisement workflow tables
- The live schema includes `advertisement_workflow_states`, `advertisement_workflow_transitions`, and `advertisement_workflow_audits`.
- `advertisement_workflow_states` and `advertisement_workflow_transitions` are seeded by `2026_07_13_000201_seed_advertisement_workflow_defaults.php` and have the expected state/transition metadata.
- The recorded live schema for `advertisement_workflow_audits` currently includes `advertisement_uuid` rather than `advertisement_id`, suggesting a schema shape that does not fully match the active migration definitions under `2026_07_13_000200_create_advertisement_workflow_and_audits.php`.
- `advertisement_workflow_audits` also contains fields like `user_role`, `action`, `reason`, `comment`, `ip`, `device`, and `extra`, showing a more audit-oriented design.

### 3. Generic workflow engine schema
- Active generic workflow tables are present in the live schema and migration files:
  - `workflow_definitions`
  - `workflow_steps`
  - `workflow_versions`
  - `workflow_transitions`
  - `workflow_instance_steps`
  - `workflow_actions`
  - `workflow_conditions`
  - `workflow_assignments`
- These tables are defined in `database/migrations/2026_07_19_000000_create_generic_workflow_tables.php` and are supported by earlier migration upgrades in `Modules/Workflow/Database/Migrations/2026_07_07_000001_create_workflow_engine_support_tables.php`.
- The generic workflow tables include multi-column unique keys and indexes, such as `uniq_workflow_transitions`, `uniq_workflow_instance_steps`, and `uniq_workflow_assignments`.

### 4. Approval engine schema
- Active approval engine tables are present in the live schema and migration files:
  - `approval_definitions`
  - `approval_steps`
  - `approval_instances`
  - `approval_instance_steps`
  - `approval_decisions`
  - `approval_delegations`
- These tables are created by `Modules/Workflow/Database/Migrations/2026_07_20_000001_create_approval_engine_phase1_tables.php`.
- Foreign key coverage is strong: approval tables mostly reference `users`, `workflow_instances`, `approval_definitions`, `approval_instances`, and `approval_steps` with `cascadeOnDelete()` and `nullOnDelete()` rules.
- Index coverage is good for common query columns such as `status`, `workflow_definition_id`, `approval_instance_id`, and `decided_at`.

### 5. Foreign key and index integrity
- The live schema metadata confirms many expected foreign keys in the workflow and approval domains.
- Advertisement-related foreign keys are good for `advertisements`, but there are missing relationship constraints in some support tables:
  - `advertisement_views.advertisement_id` is indexed but not foreign keyed.
  - `loan_offers` includes `bank_id`, `loan_plan_id`, `branch_id`, and `loan_type_id` without foreign key constraints in the active migration file.
- Generic workflow and approval tables show strong referential integrity and index design.

### 6. Legacy migration and schema drift
- A legacy migration file exists at `database/migrations/legacy/2024_01_01_000000_create_advertisement_workflow_tables.php`.
- Its table definitions closely resemble earlier advertisement workflow schema shapes and are likely a historical artifact.
- Because it is stored under `database/migrations/legacy`, it should not be treated as part of the normal migration path unless a custom migration path includes the legacy folder.
- There is a potential mismatch between live schema state and current migration definitions, especially for `advertisement_workflow_audits` and `advertisement_workflow_transitions`.

## Detailed Observations

### Active migration sources
- `2026_07_13_000000_create_advertisements_tables.php`: creates `advertisements`, `loan_offers`, `advertisement_images`, `advertisement_documents`, and `advertisement_logs`.
- `2026_07_13_000100_create_advertisement_favorites_and_views.php`: creates `advertisement_favorites` and `advertisement_views`.
- `2026_07_13_000200_create_advertisement_workflow_and_audits.php`: creates advertisement workflow and audit tables.
- `2026_07_13_000201_seed_advertisement_workflow_defaults.php`: safely evolves advertisement workflow state and transition schema and seeds defaults.
- `2026_07_19_000000_create_generic_workflow_tables.php`: creates generic workflow engine tables.
- `2026_07_19_000001_add_workflow_instance_to_advertisements.php`: links `advertisements.workflow_instance_id` to `workflow_instances`.
- `2026_07_19_000002_add_user_and_seller_user_ids_to_advertisements.php`: adds `user_id`, `seller_user_id`, and advertising metadata columns.
- `Modules/Workflow/Database/Migrations/2026_07_07_000001_create_workflow_engine_support_tables.php`: adds workflow support tables and legacy compatibility.
- `Modules/Workflow/Database/Migrations/2026_07_20_000001_create_approval_engine_phase1_tables.php`: creates approval engine tables.

### Live schema vs migration shape
- The live schema includes advertisement workflow tables with column naming and shape that are not perfectly aligned with the latest migration definitions.
- Example: active migration defines `advertisement_workflow_audits.advertisement_id` as a foreign key, but the extracted metadata shows `advertisement_uuid` and no `advertisement_id` column.
- This suggests that the current live schema may include legacy table shapes or that the metadata snapshot reflects a different state than the latest migrations.

## Recommendations

1. Align live schema and migrations
   - Confirm the current database state against the latest migration definitions.
   - If `advertisement_workflow_audits` is still using `advertisement_uuid`, add a migration to normalize it to `advertisement_id` or document the intentional legacy shape.

2. Strengthen referential integrity
   - Add a foreign key constraint for `advertisement_views.advertisement_id` to `advertisements.id` if the business model requires strict relational integrity.
   - Review `loan_offers` and consider foreign keys for `bank_id`, `loan_plan_id`, `branch_id`, and `loan_type_id` where appropriate.

3. Isolate or remove legacy migrations
   - Keep `database/migrations/legacy/2024_01_01_000000_create_advertisement_workflow_tables.php` as an archival artifact only.
   - Do not include it in standard migration paths unless intentionally applying legacy schema.
   - If the legacy path is no longer required, move the file to `docs/` or archive it outside of `database/migrations`.

4. Validate approval workflow coverage
   - Ensure approval engine tables are used by application code paths and that approval workflow transitions have clear links to generic workflow instances.
   - Verify that `workflow_instances` and `approval_instances` are populated and indexed for real usage.

5. Update documentation
   - Sync documentation to state that the active workflow system is the generic workflow engine and that legacy advertisement-specific workflow artifacts are deprecated.
   - Reference `LEGACY_WORKFLOW_ENGINE_AUDIT.md` and this audit when the migration path is reviewed.

## Conclusion
The current database schema contains a strong generic workflow and approval foundation. Advertisement domain tables are mostly well-defined, but there is evidence of legacy schema drift in advertisement workflow audit tables and some missing foreign key constraints. The legacy migration artifact should be isolated from the normal migration path, and the current live schema should be reconciled with the latest migrations to eliminate mismatch risk.

## Reconciliation Update
- `advertisement_views.advertisement_id` now has a foreign key to `advertisements.id` with `SET NULL` on delete so analytics rows are preserved when an advertisement is removed.
- The canonical advertisement workflow audit reference is `advertisement_workflow_audits.advertisement_uuid` and the application models continue to use that column as the source of truth.
- A corrective migration was added at `database/migrations/2026_07_21_000000_reconcile_advertisement_views_referential_integrity.php` and is safe to re-run on existing databases.
