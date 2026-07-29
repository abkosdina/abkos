# Phase 1 Workflow Infrastructure Plan

## Goal
Implement the first phase of a generic workflow engine without introducing a separate advertisement-only workflow system.

## Scope
- Create a reusable workflow domain for definitions, states, transitions, instances, and instance steps.
- Provide a generic engine for start/transition/complete/cancel operations.
- Keep the advertisement workflow layer compatible through an adapter rather than a separate engine.
- Preserve backward compatibility for existing advertisement workflow APIs where possible.

## Included
- Generic workflow models and migrations.
- Generic workflow repository/service abstractions.
- Core workflow engine orchestration.
- Authorization, locking, and idempotency support.
- Regression tests for start/transition and duplicate transition handling.

## Excluded for Phase 1
- Full approval engine rules.
- Condition engine evaluation.
- Action handler execution.
- Rich UI or admin workflow builder.
