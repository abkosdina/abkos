# Database Reconciliation Summary

## Original mismatch
- `advertisement_views.advertisement_id` existed in the live MySQL schema but had no foreign key constraint.
- `advertisement_workflow_audits` in the live database used `advertisement_uuid`, while older migration narratives and some documentation referenced `advertisement_id`.

## Root cause
- The advertisement view table had a relational index but no enforced relationship to `advertisements`.
- The workflow audit table had drifted toward the UUID-based advertisement reference used by the current advertisement workflow model layer.

## Final canonical schema
- `advertisement_views.advertisement_id` is now a foreign key to `advertisements.id` with `SET NULL` on delete.
- `advertisement_workflow_audits.advertisement_uuid` remains the canonical reference for advertisement-specific workflow audit rows.

## Migration created
- `database/migrations/2026_07_21_000000_reconcile_advertisement_views_referential_integrity.php`

## Models changed
- No production model changes were required because the existing Eloquent model layer already uses the UUID-based workflow audit relationship.

## Tests added
- `Modules/Advertisements/Tests/Feature/DatabaseSchemaReconciliationTest.php`

## Data preservation strategy
- Existing `advertisement_views` rows are preserved.
- Deleting an advertisement now leaves the analytics row intact by setting `advertisement_id` to `NULL`.

## Remaining risks
- The broader feature test suite still has existing failures unrelated to this reconciliation, caused by the test environment’s SQLite in-memory migrations and advertisement workflow setup assumptions.
