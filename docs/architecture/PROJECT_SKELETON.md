# Enterprise Project Skeleton

This document describes the foundation skeleton for a modular monolith fintech marketplace.

## 1. Complete folder tree

```text
app/
Modules/
  Authentication/
  Users/
  Roles/
  Permissions/
  KYC/
  Banks/
  Advertisements/
  Orders/
  Escrow/
  Wallet/
  Transactions/
  Contracts/
  Workflow/
  Documents/
  Chat/
  Complaint/
  Arbitration/
  Ratings/
  Violations/
  VIP/
  Notifications/
  Settings/
  Reports/
  Audit/
  Admin/
  Operator/
  BankEmployee/
  Shared/
Shared/
Console/
Config/
Routes/
Storage/
bootstrap/
tests/
```

## 2. Namespace strategy

- `App\\`
- `Modules\\<ModuleName>\\...`
- `Shared\\...`

Examples:
- `Modules\\Authentication\\Controllers\\LoginController`
- `Modules\\Users\\Repositories\\UserRepository`
- `Shared\\Base\\BaseRepository`

## 3. Base classes

The shared kernel provides abstract base classes for:
- `BaseModel`
- `BaseRepository`
- `BaseService`
- `BaseAction`
- `BaseDTO`
- `BaseController`
- `BasePolicy`
- `BaseNotification`
- `BaseObserver`

## 4. Service providers

Planned providers:
- `SharedServiceProvider`
- `ModuleServiceProvider`
- `EventServiceProvider`
- `RepositoryServiceProvider`

## 5. Dependency injection strategy

- Repository interfaces are bound to implementations in service providers.
- Services resolve repositories through interfaces.
- Controllers depend on services, not concrete repositories.

## 6. Repository bindings

Each module should register repository bindings using a provider, for example:
- `UserRepositoryInterface` → `UserRepository`

## 7. Module loading strategy

- Modules follow PSR-4 autoloading under `Modules/`.
- Providers are auto-discovered via Laravel package discovery where possible.
- Module routes are registered from each module's route files.

## 8. Shared kernel architecture

The `Shared` module contains:
- base abstractions
- response utilities
- exception classes
- helpers and constants
- traits and support classes

## 9. Exception architecture

- `ApiException`
- `ValidationException`
- `AuthorizationException`
- `NotFoundException`

## 10. Logging architecture

- `activity_logs`
- `audit_logs`
- `system_logs`

## 11. Testing architecture

- `tests/Unit`
- `tests/Feature`
- `tests/Integration`

## 12. Development conventions

- Controllers are orchestration-only.
- Business logic lives in services and actions.
- DTOs are used for request and response shaping.
- Events and listeners are used for decoupled workflows.
- Repositories are the single persistence boundary.
