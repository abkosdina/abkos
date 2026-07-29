# Approval Authorization Architecture

## Overview

The approval authorization layer is implemented as a generic, role-agnostic, permission-aware system that sits between the workflow approval engine and the underlying business rules. It is designed to keep approval decisions auditable, extensible, and safe while remaining framework-agnostic in its public contract.

## Core Components

### 1. ApprovalEngine
The engine remains the orchestrator of the approval lifecycle. It owns lifecycle actions such as start, approve, reject, and return-for-correction, and it delegates authorization checks to the authorization service before persisting decisions.

### 2. ApprovalAuthorizationService
This service is the central decision point for approval actions. It evaluates:
- whether the user is authenticated
- whether the approval instance is still active
- whether the current step is active
- whether the user is eligible for the step
- whether the user has already decided on that step
- whether the user is suspended or banned
- whether the action is blocked by self-approval rules
- whether the step has expired

### 3. ApproverResolverRegistry
The registry provides a plugin-style mechanism for resolving eligible approvers. Each resolver can support a different strategy such as role-based, permission-based, user-based, or dynamic behavior.

### 4. Resolver Implementations
Current resolvers include:
- RoleApproverResolver: resolves users by required role through Spatie roles
- PermissionApproverResolver: resolves users by required permission through Spatie permissions
- UserApproverResolver: resolves a single explicitly configured user
- DynamicApproverResolver: provides a configurable extension point for future dynamic approaches

### 5. Self-Approval Rules
Self-approval is enforced via a pluggable rule interface. The default implementation blocks the originator of the workflow from approving their own request. This keeps the system generic while allowing future custom rules.

## Decision Flow

1. The engine identifies the active approval step.
2. The engine asks the authorization service whether the current user may act.
3. The authorization service resolves eligible approvers through the registry.
4. The service checks business rules, including eligibility, suspension, duplicate decisions, and self-approval.
5. If authorized, the engine persists the decision and advances the workflow state.

## Security Characteristics

- Authorization is enforced before any decision is stored.
- The system blocks duplicate decisions on the same approval step.
- Self-approval is configurable and defaults to blocking the requester.
- Approval actions are protected by explicit exceptions and policies.
- The architecture is extensible: adding a new resolver or self-approval rule does not require changing the engine core.

## Policies

Authorization policies are registered for the main workflow entities so that additional application-layer authorization can be enforced consistently.

## Test Coverage

The implementation currently includes targeted unit tests for:
- core lifecycle operations
- role-based and permission-based authorization
- specific-user approval paths
- duplicate decision prevention
- suspended user blocking
- delegation authorization
