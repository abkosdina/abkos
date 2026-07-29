# Approval Authorization Architecture

## Overview

The approval authorization layer was introduced to make approval decisions secure, generic, and extensible. It sits between the workflow approval engine and the business rules that determine who is allowed to act on a step.

## Components

### 1. ApprovalAuthorizationService
This service is the single authorization gateway for approval actions. It evaluates:
- authentication
- step and instance activity
- eligibility for the current approval step
- duplicate decision prevention
- suspension and ban state
- self-approval rules
- expiration rules

### 2. ApproverResolverRegistry
The registry provides a strategy-based mechanism for resolving eligible approvers. It allows different resolver implementations to be plugged in without modifying the core engine.

### 3. Resolvers
Current resolver implementations include:
- RoleApproverResolver
- PermissionApproverResolver
- UserApproverResolver
- DynamicApproverResolver

### 4. SelfApproval Rules
A pluggable self-approval rule system is available. The default implementation blocks the workflow originator from approving their own request.

## Flow

1. The engine identifies the active approval step.
2. The authorization service resolves eligible approvers.
3. The service checks policy-driven constraints.
4. If allowed, the engine persists the decision and advances the workflow state.

## Security Characteristics

- Authorization is enforced before any decision is written.
- Duplicate approvals are blocked at the step level.
- Self-approval is blocked by default.
- The architecture is extensible for future dynamic approver strategies.

## Tests

Coverage exists for role-based, permission-based, specific-user, duplicate-decision, suspended-user, and delegation authorization paths.
