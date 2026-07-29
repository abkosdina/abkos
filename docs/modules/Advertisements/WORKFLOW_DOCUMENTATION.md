# Advertisement Workflow Module - Current Architecture Documentation

## Status

This document describes the current advertisement workflow implementation. The active architecture uses the generic workflow engine rather than the deprecated advertisement-specific workflow engine.

## Current Architecture

The active workflow path is:

AdvertisementWorkflowService
    ↓
AdvertisementWorkflowAdapter
    ↓
Generic App\Services\Workflow\WorkflowEngine
    ↓
WorkflowDefinition / WorkflowState / WorkflowTransition
    ↓
WorkflowInstance / WorkflowInstanceStep

## Core Components

- Generic workflow engine: `app/Services/Workflow/WorkflowEngine.php`
- Workflow definition and state models: `WorkflowDefinition`, `WorkflowState`, `WorkflowTransition`
- Runtime models: `WorkflowInstance`, `WorkflowInstanceStep`
- Advertisement adapter: `Modules/Advertisements/Adapters/AdvertisementWorkflowAdapter.php`
- Advertisement service: `Modules/Advertisements/Services/AdvertisementWorkflowService.php`

## What the Generic Engine Provides

The generic engine is responsible for:

- loading workflow definitions from the database
- creating workflow instances for entities such as advertisements
- validating transitions against defined rules
- executing transitions transactionally
- recording workflow instance steps for auditability
- enforcing idempotency and concurrency protection
- emitting workflow events

## Advertisement Integration

The advertisement module stays domain-focused by using the adapter layer. The adapter translates advertisement actions such as submit, approve, reject, publish, pause, resume, archive, restore, and sold into calls against the generic workflow engine.

## Authorization and Audit

Authorization remains enforced at the application boundary through controllers, services, policies, and request validation. Every successful transition is recorded as a workflow instance step so the system retains an auditable trail.

## Legacy Note

The legacy advertisement-specific workflow engine remains in the repository only as a deprecated compatibility artifact. It is not part of the active architecture and should not be used for new implementation work.

## Migration Guidance

When adding new workflow behavior:

1. add or update workflow definitions and transitions in the database
2. implement advertisement-specific mapping in the adapter
3. keep domain-specific business rules in the advertisement service layer
4. prefer the generic engine over the deprecated legacy class

