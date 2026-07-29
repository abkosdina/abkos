# Enterprise Module Map

This document captures the modular monolith structure for the financial marketplace platform foundation.

## Module Inventory

- Authentication
- Users
- Roles
- Permissions
- KYC
- Banks
- Advertisements
- Orders
- Escrow
- Wallet
- Transactions
- Contracts
- Workflow
- Documents
- Chat
- Complaint
- Arbitration
- Ratings
- Violations
- VIP
- Notifications
- Settings
- Reports
- Audit
- Admin
- Operator
- BankEmployee
- Shared

## Module Conventions

Each module should expose a consistent internal structure:

- Config/
- Database/
- Models/
- Enums/
- Repositories/
- Interfaces/
- Services/
- Actions/
- Policies/
- Requests/
- Resources/
- Events/
- Listeners/
- Notifications/
- Jobs/
- DTO/
- Exceptions/
- Observers/
- Routes/
- Controllers/
- Tests/

Each module may also provide a dedicated architecture document in `docs/architecture/<MODULE>_MODULE.md` for module-specific design and API details.

## Recommended Namespace Convention

- `Modules\\<ModuleName>\\Actions`
- `Modules\\<ModuleName>\\Services`
- `Modules\\<ModuleName>\\Repositories`
- `Modules\\<ModuleName>\\DTO`
- `Modules\\<ModuleName>\\Requests`
- `Modules\\<ModuleName>\\Resources`
- `Modules\\<ModuleName>\\Policies`
- `Modules\\<ModuleName>\\Events`
- `Modules\\<ModuleName>\\Listeners`
- `Modules\\<ModuleName>\\Notifications`
- `Modules\\<ModuleName>\\Jobs`

## Dependency Rules

- Core business rules belong in services and actions.
- Bank-specific catalog, loan-product, and plan definitions belong under the Banks module.
- The Advertisements module should remain focused on ad lifecycle, discovery, and interaction rather than banking or loan-plan ownership.
- Controllers remain orchestration-only.
- Modules interact through contracts, events, and workflow-driven transitions.
- The Shared module contains reusable abstractions and cross-cutting concerns.
