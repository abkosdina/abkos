# Ledger Module

The Ledger module is the financial source of truth for the platform. It implements immutable double-entry accounting and provides the foundation for Wallet, Escrow, Payments, Refunds, Commission, Withdrawals, Settlements, and future banking integrations.

## Core Concepts

- `accounts` and `account_types` define ledger accounts and ownership.
- `ledger_transactions` represent financial events.
- `ledger_entries` store debit/credit entry lines.
- `journal_entries` capture posting activity for audit and review.
- Balances are derived from ledger entries and snapshots.
- Reversals are created through reversing transactions rather than deletion.

## Services

- `LedgerService`
- `AccountingService`
- `BalanceService`
- `JournalService`
- `TransactionService`

## Repository Pattern

- `AccountRepository`
- `LedgerRepository`
- `BalanceRepository`
- `JournalRepository`

## Events

- `LedgerTransactionCreated`
- `LedgerPosted`
- `LedgerReversed`
- `BalanceUpdated`

## API

Routes are exposed under `/api/v1/ledger`.
