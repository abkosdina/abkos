# Modules

This directory contains the modular monolith foundation for the platform.

## Current Foundation Modules

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

Each module is expected to follow the layered structure described in [docs/architecture/PROJECT_FOUNDATION.md](../docs/architecture/PROJECT_FOUNDATION.md).

Bank-related catalog and loan-plan concepts belong under the Banks module. The Advertisements module should remain focused on ad creation, editing, lifecycle, discovery, and interaction only.
