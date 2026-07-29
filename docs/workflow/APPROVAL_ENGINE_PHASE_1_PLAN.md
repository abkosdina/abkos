# Approval Engine Phase 1 Plan

## Overview

This phase creates the database and domain foundation for a generic approval engine that is independent from the workflow engine lifecycle. The workflow engine remains responsible for state and transitions; the approval engine will own approval requirements, approval steps, approver data, approval decisions, and delegation metadata.

## Existing Workflow Architecture

The project already contains a generic workflow engine built around:

- workflow_definitions
- workflow_states
- workflow_transitions
- workflow_instances
- workflow_instance_steps

The current active architecture flows as:

Business Entity
    ↓
Workflow Definition
    ↓
Workflow Instance
    ↓
Generic Workflow Engine

The new approval foundation is attached to this existing workflow model instead of introducing a second workflow system.

## Approval Engine Integration Point

The approval foundation integrates at the workflow-instance level through:

- approval_definitions (one per workflow definition)
- approval_instances (one per workflow instance)
- approval_steps (definition-level approval gates)
- approval_instance_steps (runtime step instances)
- approval_decisions (audit-friendly decisions)
- approval_delegations (foundation-only delegation metadata)

This preserves a clean separation of concerns:

- Workflow Engine = state, transitions, lifecycle
- Approval Engine = approval requirements, approver routing, decision capture

## Database Design

### Tables

- approval_definitions
- approval_steps
- approval_instances
- approval_instance_steps
- approval_decisions
- approval_delegations

### Key design choices

- UUIDs are used for all approval entities
- Workflow definitions and workflow instances remain the primary link to the generic workflow engine
- Approval definitions are scoped to a workflow definition and keyed uniquely within that scope
- Decisions support immutable audit fields and idempotency keys
- Delegation tables are foundation-only and do not implement behavior yet

## Models

The implementation introduces:

- ApprovalDefinition
- ApprovalStep
- ApprovalInstance
- ApprovalInstanceStep
- ApprovalDecision
- ApprovalDelegation

All models use the existing module-based namespace conventions and the shared base model pattern.

## Relationships

### ApprovalDefinition

- belongsTo WorkflowDefinition
- hasMany ApprovalSteps
- hasMany ApprovalInstances
- belongsTo creator user
- belongsTo updater user

### ApprovalStep

- belongsTo ApprovalDefinition
- hasMany ApprovalInstanceSteps
- belongsTo required user when a specific user is required

### ApprovalInstance

- belongsTo WorkflowInstance
- belongsTo ApprovalDefinition
- hasMany ApprovalInstanceSteps
- hasMany ApprovalDecisions
- hasMany ApprovalDelegations

### ApprovalInstanceStep

- belongsTo ApprovalInstance
- belongsTo ApprovalStep
- hasMany ApprovalDecisions
- hasMany ApprovalDelegations

### ApprovalDecision

- belongsTo ApprovalInstance
- belongsTo ApprovalInstanceStep
- belongsTo approver user
- belongsTo delegated_from user

### ApprovalDelegation

- belongsTo ApprovalInstance
- belongsTo ApprovalInstanceStep
- belongsTo delegated_from user
- belongsTo delegated_to user
- belongsTo creator user

## Foreign Keys and Constraints

The migration includes:

- foreign keys to workflow_definitions and workflow_instances
- foreign keys to users for creators, approvers, and delegation participants
- unique constraints for UUIDs and approval-definition keys
- unique constraint for approval decision idempotency key
- indexes for lookup and filtering performance

## UUID Strategy

The project already uses UUIDs for core domain entities. The approval foundation follows the same convention by using UUID columns with the Eloquent HasUuids trait on each model.

## Future Extension Points

The phase-one foundation is intentionally generic so later work can add:

- approval lifecycle services
- approval actions and APIs
- authorization and policy enforcement
- delegation behavior
- expiration logic
- notifications and events

## Migration Strategy

The implementation uses a dedicated migration in the workflow module so it can run safely alongside the existing generic workflow migration. The migration checks for existing tables before creating them and avoids any destructive changes to unrelated tables.
