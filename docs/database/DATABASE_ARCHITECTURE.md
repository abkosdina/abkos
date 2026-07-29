# Enterprise Database Architecture for Financial Marketplace

This document defines a production-ready MySQL database architecture for a large-scale financial marketplace platform with OTP authentication, workflow-driven processes, escrow, wallet, contracts, KYC, audits, notifications, complaints, and multi-role administration.

The schema is implemented as Laravel migration files under [database/migrations](../migrations) and is ready to be executed once a MySQL database is available.

## 1. Complete ERD Description

The database is designed around a modular, secure, and auditable domain model.

### Core design principles
- All important tables include:
  - `id`
  - `uuid`
  - `created_at`
  - `updated_at`
  - `deleted_at`
  - `created_by`
  - `updated_by`
  - `deleted_by`
- Financial, compliance, and workflow tables are soft-deletable and auditable.
- Immutable audit trails are stored separately.
- All status columns use enums or constrained values.
- The schema supports millions of rows through indexing, partitioning, and normalized structure.

### Core entities
- Identity and access: users, roles, permissions, sessions, OTP
- Profile and KYC: user profiles, signatures, KYC submissions, documents
- Organization: banks, bank employees
- Marketplace: loan products, installment plans, advertisements, negotiation, orders
- Financial core: wallet, wallet transactions, escrow, escrow transactions, payments, commissions
- Workflow: workflow definitions, steps, instances, approvals, logs, events
- Communication: notifications, SMS, chat rooms, messages, attachments
- Trust and governance: ratings, reviews, complaints, arbitration, violations, VIP, reports
- Platform operations: settings, media, files, activity logs, audit logs, system logs

---

## 2. Table Dependency Diagram

High-level dependency order:

1. Identity layer
   - `roles`
   - `permissions`
   - `users`
   - `role_user`
   - `permission_role`
   - `user_profiles`
   - `user_sessions`
   - `otp_requests`

2. Compliance layer
   - `kyc_submissions`
   - `kyc_documents`
   - `user_signatures`

3. Banking layer
   - `banks`
   - `bank_employees`

4. Marketplace layer
   - `loan_products`
   - `bank_loan_products`
   - `advertisements`
   - `advertisement_images`
   - `negotiations`
   - `orders`
   - `order_timeline`

5. Financial layer
   - `wallet`
   - `wallet_transactions`
   - `escrow`
   - `escrow_transactions`
   - `payment_records`
   - `commission_rules`
   - `commission_transactions`

6. Contract and workflow layer
   - `contracts`
   - `contract_versions`
   - `workflow_definitions`
   - `workflow_steps`
   - `workflow_instances`
   - `workflow_logs`
   - `workflow_approvals`
   - `workflow_events`

7. Communication and file layer
   - `document_types`
   - `documents`
   - `document_approvals`
   - `notifications`
   - `sms_logs`
   - `chat_rooms`
   - `chat_messages`
   - `chat_attachments`
   - `media`
   - `files`

8. Trust and governance layer
   - `ratings`
   - `reviews`
   - `complaints`
   - `complaint_messages`
   - `arbitrations`
   - `violations`
   - `vip_memberships`

9. Operations layer
   - `reports`
   - `activity_logs`
   - `audit_logs`
   - `system_logs`
   - `settings`

---

## 3. Creation Order of Tables

Recommended migration order:

1. `roles`
2. `permissions`
3. `users`
4. `role_user`
5. `permission_role`
6. `user_profiles`
7. `user_sessions`
8. `otp_requests`
9. `user_signatures`
10. `banks`
11. `bank_employees`
12. `kyc_submissions`
13. `kyc_documents`
14. `loan_products`
15. `bank_loan_products`
16. `advertisements`
17. `advertisement_images`
18. `negotiations`
19. `orders`
20. `order_timeline`
21. `wallet`
22. `wallet_transactions`
23. `escrow`
24. `escrow_transactions`
25. `payment_records`
26. `commission_rules`
27. `commission_transactions`
28. `contracts`
29. `contract_versions`
30. `workflow_definitions`
31. `workflow_steps`
32. `workflow_instances`
33. `workflow_logs`
34. `workflow_approvals`
35. `workflow_events`
36. `document_types`
37. `documents`
38. `document_approvals`
39. `notifications`
40. `sms_logs`
41. `chat_rooms`
42. `chat_messages`
43. `chat_attachments`
44. `ratings`
45. `reviews`
46. `complaints`
47. `complaint_messages`
48. `arbitrations`
49. `violations`
50. `vip_memberships`
51. `reports`
52. `activity_logs`
53. `audit_logs`
54. `system_logs`
55. `settings`
56. `media`
57. `files`

---

## 4. Relationship Matrix

| Parent | Child | Relationship |
|---|---|---|
| `users` | `user_profiles` | 1:1 |
| `users` | `user_sessions`, `otp_requests`, `wallet`, `orders`, `complaints`, `notifications` | 1:N |
| `roles` | `role_user` | 1:N |
| `permissions` | `permission_role` | 1:N |
| `banks` | `bank_employees`, `loan_products` | 1:N |
| `loan_products` | `bank_loan_products`, `advertisements` | 1:N |
| `advertisements` | `advertisement_images`, `orders`, `negotiations` | 1:N |
| `orders` | `order_timeline`, `contracts`, `escrow`, `payment_records`, `workflow_instances`, `ratings` | 1:N |
| `contracts` | `contract_versions` | 1:N |
| `wallet` | `wallet_transactions` | 1:N |
| `escrow` | `escrow_transactions` | 1:N |
| `workflow_definitions` | `workflow_steps`, `workflow_instances` | 1:N |
| `workflow_instances` | `workflow_logs`, `workflow_approvals`, `workflow_events` | 1:N |
| `document_types` | `documents` | 1:N |
| `documents` | `document_approvals` | 1:N |
| `chat_rooms` | `chat_messages` | 1:N |
| `chat_messages` | `chat_attachments` | 1:N |
| `complaints` | `complaint_messages`, `arbitrations` | 1:N |
| `users` | `ratings`, `reviews`, `violations`, `vip_memberships`, `reports` | 1:N |
| `media` | `files` | 1:N |

### Polymorphic relationships
- `notifications`: `notifiable_type`, `notifiable_id`
- `activity_logs`, `audit_logs`, `system_logs`: `subject_type`, `subject_id`
- `files`: `fileable_type`, `fileable_id`
- `media`: `model_type`, `model_id`

---

## 5. Table Specification

The following tables describe the full schema.

### 5.1 Identity and Access

#### roles
- Purpose: system roles for user, admin, operator, bank employee, auditor
- Columns:
  - `id` bigint unsigned PK
  - `uuid` char(36) unique
  - `name` varchar(100)
  - `slug` varchar(100)
  - `display_name` varchar(150)
  - `description` text nullable
  - `guard_name` varchar(50)
  - `is_system` boolean default false
  - `status` varchar(30)
  - `created_at`, `updated_at`, `deleted_at`
  - `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_roles_slug (slug)`, `idx_roles_status (status)`, `idx_roles_name_guard (name, guard_name)`
- Foreign Keys: none
- Unique Constraints: `uniq_roles_slug_guard (slug, guard_name)`
- Nullable Fields: `description`
- Soft Deletes: yes
- Audit Columns: yes

#### permissions
- Purpose: permissions for authorization
- Columns:
  - `id`, `uuid`, `name`, `slug`, `group_name`, `description`, `guard_name`, `status`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_permissions_slug (slug)`, `idx_permissions_group (group_name)`, `idx_permissions_status (status)`
- Foreign Keys: none
- Unique Constraints: `uniq_permissions_slug_guard (slug, guard_name)`
- Nullable Fields: `description`
- Soft Deletes: yes
- Audit Columns: yes

#### users
- Purpose: primary user account records
- Columns:
  - `id`, `uuid`, `username`, `email`, `phone`, `password_hash`, `status`, `email_verified_at`, `phone_verified_at`, `last_login_at`, `locale`, `timezone`, `is_active`, `is_locked`, `lock_reason`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_users_email (email)`, `idx_users_phone (phone)`, `idx_users_status (status)`, `idx_users_active_status (is_active, status)`, `fulltext idx_users_username (username)`
- Foreign Keys: none
- Unique Constraints: `uniq_users_email (email)`, `uniq_users_phone (phone)`, `uniq_users_username (username)`
- Nullable Fields: `username`, `email`, `phone`, `password_hash`, `email_verified_at`, `phone_verified_at`, `last_login_at`, `locale`, `timezone`, `lock_reason`
- Soft Deletes: yes
- Audit Columns: yes

#### role_user
- Purpose: many-to-many user-role mapping
- Columns:
  - `id`, `uuid`, `user_id`, `role_id`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_role_user_user (user_id)`, `idx_role_user_role (role_id)`, `idx_role_user_user_role (user_id, role_id)`
- Foreign Keys: `user_id -> users.id`, `role_id -> roles.id`
- Unique Constraints: `uniq_role_user (user_id, role_id)`
- Nullable Fields: none
- Soft Deletes: yes
- Audit Columns: yes

#### permission_role
- Purpose: many-to-many role-permission mapping
- Columns:
  - `id`, `uuid`, `role_id`, `permission_id`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_permission_role_role (role_id)`, `idx_permission_role_permission (permission_id)`
- Foreign Keys: `role_id -> roles.id`, `permission_id -> permissions.id`
- Unique Constraints: `uniq_permission_role (role_id, permission_id)`
- Nullable Fields: none
- Soft Deletes: yes
- Audit Columns: yes

#### user_profiles
- Purpose: extended profile information
- Columns:
  - `id`, `uuid`, `user_id`, `first_name`, `last_name`, `national_id`, `date_of_birth`, `gender`, `address_line_1`, `address_line_2`, `city`, `state`, `country`, `postal_code`, `avatar_url`, `bio`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_user_profiles_user (user_id)`, `idx_user_profiles_national_id (national_id)`, `idx_user_profiles_country (country)`
- Foreign Keys: `user_id -> users.id`
- Unique Constraints: `uniq_user_profiles_user (user_id)`, `uniq_user_profiles_national_id (national_id)`
- Nullable Fields: most personal fields
- Soft Deletes: yes
- Audit Columns: yes

#### user_sessions
- Purpose: active auth sessions
- Columns:
  - `id`, `uuid`, `user_id`, `session_token`, `ip_address`, `user_agent`, `last_activity_at`, `expires_at`, `revoked_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_user_sessions_user (user_id)`, `idx_user_sessions_token (session_token)`, `idx_user_sessions_expires (expires_at)`
- Foreign Keys: `user_id -> users.id`
- Unique Constraints: `uniq_user_sessions_token (session_token)`
- Nullable Fields: `revoked_at`, `last_activity_at`
- Soft Deletes: yes
- Audit Columns: yes

#### otp_requests
- Purpose: OTP delivery and verification
- Columns:
  - `id`, `uuid`, `user_id`, `channel`, `phone`, `email`, `code_hash`, `purpose`, `status`, `sent_at`, `expires_at`, `verified_at`, `attempt_count`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_otp_user (user_id)`, `idx_otp_phone (phone)`, `idx_otp_status (status)`, `idx_otp_phone_status_expires (phone, status, expires_at)`
- Foreign Keys: `user_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `user_id`, `phone`, `email`, `verified_at`
- Soft Deletes: yes
- Audit Columns: yes

#### user_signatures
- Purpose: electronic signatures for contracts and approvals
- Columns:
  - `id`, `uuid`, `user_id`, `signature_path`, `signature_type`, `signed_at`, `is_verified`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_user_signatures_user (user_id)`, `idx_user_signatures_signed_at (signed_at)`
- Foreign Keys: `user_id -> users.id`
- Unique Constraints: `uniq_user_signatures_user (user_id)`
- Nullable Fields: `signature_path`, `signed_at`
- Soft Deletes: yes
- Audit Columns: yes

### 5.2 KYC and Compliance

#### kyc_submissions
- Purpose: KYC application lifecycle
- Columns:
  - `id`, `uuid`, `user_id`, `submitted_at`, `reviewed_at`, `status`, `reviewer_id`, `notes`, `risk_score`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_kyc_user (user_id)`, `idx_kyc_status (status)`, `idx_kyc_reviewer (reviewer_id)`, `idx_kyc_status_submitted (status, submitted_at)`
- Foreign Keys: `user_id -> users.id`, `reviewer_id -> users.id`
- Unique Constraints: `uniq_kyc_user (user_id)`
- Nullable Fields: `reviewed_at`, `reviewer_id`, `notes`, `risk_score`
- Soft Deletes: yes
- Audit Columns: yes

#### kyc_documents
- Purpose: KYC evidence files
- Columns:
  - `id`, `uuid`, `kyc_submission_id`, `document_type`, `file_path`, `mime_type`, `status`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_kyc_documents_submission (kyc_submission_id)`, `idx_kyc_documents_type (document_type)`, `idx_kyc_documents_status (status)`
- Foreign Keys: `kyc_submission_id -> kyc_submissions.id`
- Unique Constraints: none
- Nullable Fields: `file_path`, `verified_at`
- Soft Deletes: yes
- Audit Columns: yes

### 5.3 Bank and Product Domain

#### banks
- Purpose: participating financial institutions
- Columns:
  - `id`, `uuid`, `name`, `slug`, `code`, `status`, `country`, `address`, `contact_email`, `contact_phone`, `is_verified`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_banks_slug (slug)`, `idx_banks_code (code)`, `idx_banks_status (status)`, `idx_banks_country (country)`
- Foreign Keys: none
- Unique Constraints: `uniq_banks_slug (slug)`, `uniq_banks_code (code)`
- Nullable Fields: `address`, `contact_email`, `contact_phone`
- Soft Deletes: yes
- Audit Columns: yes

#### bank_employees
- Purpose: bank employee accounts
- Columns:
  - `id`, `uuid`, `user_id`, `bank_id`, `position`, `department`, `employee_code`, `status`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_bank_employees_user (user_id)`, `idx_bank_employees_bank (bank_id)`, `idx_bank_employees_status (status)`
- Foreign Keys: `user_id -> users.id`, `bank_id -> banks.id`
- Unique Constraints: `uniq_bank_employees_user (user_id)`, `uniq_bank_employees_code (employee_code)`
- Nullable Fields: `department`
- Soft Deletes: yes
- Audit Columns: yes

#### loan_products
- Purpose: bank loan products catalog
- Columns:
  - `id`, `uuid`, `bank_id`, `name`, `slug`, `description`, `currency`, `min_amount`, `max_amount`, `interest_rate`, `duration_months`, `status`, `is_public`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_loan_products_bank (bank_id)`, `idx_loan_products_status (status)`, `idx_loan_products_currency (currency)`, `fulltext idx_loan_products_search (name, description)`
- Foreign Keys: `bank_id -> banks.id`
- Unique Constraints: `uniq_loan_products_slug (slug)`
- Nullable Fields: `description`
- Soft Deletes: yes
- Audit Columns: yes

#### bank_loan_products
- Purpose: installment options for loan products
- Columns:
  - `id`, `uuid`, `loan_product_id`, `name`, `duration_months`, `installment_count`, `interest_rate`, `down_payment_percent`, `status`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_bank_loan_products_product (loan_product_id)`, `idx_bank_loan_products_status (status)`, `idx_bank_loan_products_product_duration (loan_product_id, duration_months)`
- Foreign Keys: `loan_product_id -> loan_products.id`
- Unique Constraints: none
- Nullable Fields: none
- Soft Deletes: yes
- Audit Columns: yes

#### advertisements
- Purpose: loan privilege advertisements sold on marketplace
- Columns:
  - `id`, `uuid`, `loan_product_id`, `seller_user_id`, `title`, `description`, `price`, `currency`, `status`, `published_at`, `expires_at`, `visibility`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_ads_product (loan_product_id)`, `idx_ads_seller (seller_user_id)`, `idx_ads_status (status)`, `idx_ads_published (published_at)`, `idx_ads_status_expires (status, expires_at)`, `fulltext idx_ads_search (title, description)`
- Foreign Keys: `loan_product_id -> loan_products.id`, `seller_user_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `description`, `published_at`, `expires_at`
- Soft Deletes: yes
- Audit Columns: yes

#### advertisement_images
- Purpose: images attached to advertisements
- Columns:
  - `id`, `uuid`, `advertisement_id`, `file_path`, `mime_type`, `sort_order`, `is_primary`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_ad_images_ad (advertisement_id)`, `idx_ad_images_primary (is_primary)`
- Foreign Keys: `advertisement_id -> advertisements.id`
- Unique Constraints: none
- Nullable Fields: `file_path`
- Soft Deletes: yes
- Audit Columns: yes

#### negotiations
- Purpose: buyer-seller negotiation records
- Columns:
  - `id`, `uuid`, `advertisement_id`, `initiator_user_id`, `counterparty_user_id`, `proposed_price`, `currency`, `status`, `expires_at`, `last_message_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_negotiations_ad (advertisement_id)`, `idx_negotiations_initiator (initiator_user_id)`, `idx_negotiations_counterparty (counterparty_user_id)`, `idx_negotiations_status (status)`
- Foreign Keys: `advertisement_id -> advertisements.id`, `initiator_user_id -> users.id`, `counterparty_user_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `expires_at`, `last_message_at`
- Soft Deletes: yes
- Audit Columns: yes

### 5.4 Orders and Contracts

#### orders
- Purpose: order lifecycle for a purchased ad
- Columns:
  - `id`, `uuid`, `advertisement_id`, `buyer_user_id`, `seller_user_id`, `order_number`, `amount`, `currency`, `status`, `accepted_at`, `completed_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_orders_ad (advertisement_id)`, `idx_orders_buyer (buyer_user_id)`, `idx_orders_seller (seller_user_id)`, `idx_orders_status (status)`, `idx_orders_number (order_number)`, `idx_orders_status_created (status, created_at)`
- Foreign Keys: `advertisement_id -> advertisements.id`, `buyer_user_id -> users.id`, `seller_user_id -> users.id`
- Unique Constraints: `uniq_orders_number (order_number)`
- Nullable Fields: `accepted_at`, `completed_at`
- Soft Deletes: yes
- Audit Columns: yes

#### order_timeline
- Purpose: immutable order lifecycle history
- Columns:
  - `id`, `uuid`, `order_id`, `event_type`, `event_status`, `comment`, `performed_by`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_order_timeline_order (order_id)`, `idx_order_timeline_event (event_type)`, `idx_order_timeline_order_created (order_id, created_at)`
- Foreign Keys: `order_id -> orders.id`, `performed_by -> users.id`
- Unique Constraints: none
- Nullable Fields: `comment`, `performed_by`
- Soft Deletes: yes
- Audit Columns: yes

#### contracts
- Purpose: electronic contract header
- Columns:
  - `id`, `uuid`, `order_id`, `contract_number`, `template_version`, `status`, `signed_at`, `expires_at`, `hash_value`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_contracts_order (order_id)`, `idx_contracts_number (contract_number)`, `idx_contracts_status (status)`, `idx_contracts_status_signed (status, signed_at)`
- Foreign Keys: `order_id -> orders.id`
- Unique Constraints: `uniq_contracts_number (contract_number)`
- Nullable Fields: `signed_at`, `expires_at`, `hash_value`
- Soft Deletes: yes
- Audit Columns: yes

#### contract_versions
- Purpose: versioned contract revisions
- Columns:
  - `id`, `uuid`, `contract_id`, `version_number`, `content`, `created_by_user_id`, `approved_at`, `hash_value`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_contract_versions_contract (contract_id)`, `idx_contract_versions_version (version_number)`, `idx_contract_versions_contract_version (contract_id, version_number)`
- Foreign Keys: `contract_id -> contracts.id`, `created_by_user_id -> users.id`
- Unique Constraints: `uniq_contract_versions_contract_version (contract_id, version_number)`
- Nullable Fields: `content`, `approved_at`, `hash_value`
- Soft Deletes: yes
- Audit Columns: yes

### 5.5 Financial Core

#### wallet
- Purpose: per-user balance container
- Columns:
  - `id`, `uuid`, `user_id`, `currency`, `balance`, `available_balance`, `pending_balance`, `status`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_wallet_user (user_id)`, `idx_wallet_currency (currency)`, `idx_wallet_status (status)`
- Foreign Keys: `user_id -> users.id`
- Unique Constraints: `uniq_wallet_user (user_id)`
- Nullable Fields: none
- Soft Deletes: yes
- Audit Columns: yes

#### wallet_transactions
- Purpose: wallet deposits, withdrawals, refunds, and fees
- Columns:
  - `id`, `uuid`, `wallet_id`, `order_id`, `transaction_type`, `amount`, `currency`, `status`, `reference_number`, `description`, `posted_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_wallet_tx_wallet (wallet_id)`, `idx_wallet_tx_order (order_id)`, `idx_wallet_tx_type (transaction_type)`, `idx_wallet_tx_status (status)`, `idx_wallet_tx_reference (reference_number)`, `idx_wallet_tx_wallet_created (wallet_id, created_at)`
- Foreign Keys: `wallet_id -> wallet.id`, `order_id -> orders.id`
- Unique Constraints: `uniq_wallet_tx_reference (reference_number)`
- Nullable Fields: `order_id`, `description`
- Soft Deletes: yes
- Audit Columns: yes

#### escrow
- Purpose: escrow record for order protection
- Columns:
  - `id`, `uuid`, `order_id`, `escrow_number`, `amount`, `currency`, `status`, `funded_at`, `released_at`, `disputed_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_escrow_order (order_id)`, `idx_escrow_number (escrow_number)`, `idx_escrow_status (status)`, `idx_escrow_created (created_at)`
- Foreign Keys: `order_id -> orders.id`
- Unique Constraints: `uniq_escrow_number (escrow_number)`
- Nullable Fields: `funded_at`, `released_at`, `disputed_at`
- Soft Deletes: yes
- Audit Columns: yes

#### escrow_transactions
- Purpose: escrow movement events
- Columns:
  - `id`, `uuid`, `escrow_id`, `transaction_type`, `amount`, `currency`, `status`, `reference_number`, `description`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_escrow_tx_escrow (escrow_id)`, `idx_escrow_tx_type (transaction_type)`, `idx_escrow_tx_status (status)`, `idx_escrow_tx_reference (reference_number)`
- Foreign Keys: `escrow_id -> escrow.id`
- Unique Constraints: `uniq_escrow_tx_reference (reference_number)`
- Nullable Fields: `description`
- Soft Deletes: yes
- Audit Columns: yes

#### payment_records
- Purpose: payment gateway settlement records
- Columns:
  - `id`, `uuid`, `order_id`, `gateway_name`, `gateway_reference`, `amount`, `currency`, `status`, `payment_method`, `processed_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_payment_records_order (order_id)`, `idx_payment_records_gateway_reference (gateway_reference)`, `idx_payment_records_status (status)`, `idx_payment_records_processed_at (processed_at)`
- Foreign Keys: `order_id -> orders.id`
- Unique Constraints: `uniq_payment_gateway_reference (gateway_reference)`
- Nullable Fields: `processed_at`
- Soft Deletes: yes
- Audit Columns: yes

#### commission_rules
- Purpose: commission policy rules
- Columns:
  - `id`, `uuid`, `name`, `rule_type`, `currency`, `percentage`, `fixed_amount`, `min_amount`, `max_amount`, `applies_to`, `status`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_commission_rules_type (rule_type)`, `idx_commission_rules_currency (currency)`, `idx_commission_rules_status (status)`
- Foreign Keys: none
- Unique Constraints: `uniq_commission_rules_name (name)`
- Nullable Fields: `fixed_amount`, `min_amount`, `max_amount`
- Soft Deletes: yes
- Audit Columns: yes

#### commission_transactions
- Purpose: commission charges and settlements
- Columns:
  - `id`, `uuid`, `order_id`, `commission_rule_id`, `amount`, `currency`, `status`, `calculated_at`, `settled_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_commission_tx_order (order_id)`, `idx_commission_tx_rule (commission_rule_id)`, `idx_commission_tx_status (status)`
- Foreign Keys: `order_id -> orders.id`, `commission_rule_id -> commission_rules.id`
- Unique Constraints: none
- Nullable Fields: `settled_at`
- Soft Deletes: yes
- Audit Columns: yes

### 5.6 Workflow Engine

#### workflow_definitions
- Purpose: reusable workflow templates
- Columns:
  - `id`, `uuid`, `name`, `slug`, `description`, `version`, `entity_type`, `is_active`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_workflow_defs_slug (slug)`, `idx_workflow_defs_active (is_active)`, `idx_workflow_defs_entity (entity_type)`
- Foreign Keys: none
- Unique Constraints: `uniq_workflow_defs_slug_version (slug, version)`
- Nullable Fields: `description`
- Soft Deletes: yes
- Audit Columns: yes

#### workflow_steps
- Purpose: workflow steps per definition
- Columns:
  - `id`, `uuid`, `workflow_definition_id`, `step_number`, `name`, `role_slug`, `approval_type`, `transition_action`, `is_required`, `timeout_hours`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_workflow_steps_definition (workflow_definition_id)`, `idx_workflow_steps_number (step_number)`, `idx_workflow_steps_definition_number (workflow_definition_id, step_number)`
- Foreign Keys: `workflow_definition_id -> workflow_definitions.id`
- Unique Constraints: `uniq_workflow_steps_definition_number (workflow_definition_id, step_number)`
- Nullable Fields: `role_slug`
- Soft Deletes: yes
- Audit Columns: yes

#### workflow_instances
- Purpose: workflow runs for orders, KYC, complaints, and more
- Columns:
  - `id`, `uuid`, `workflow_definition_id`, `entity_type`, `entity_id`, `current_step_id`, `status`, `started_at`, `completed_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_workflow_instances_definition (workflow_definition_id)`, `idx_workflow_instances_entity (entity_type, entity_id)`, `idx_workflow_instances_status (status)`, `idx_workflow_instances_current_step (current_step_id)`
- Foreign Keys: `workflow_definition_id -> workflow_definitions.id`, `current_step_id -> workflow_steps.id`
- Unique Constraints: none
- Nullable Fields: `current_step_id`, `completed_at`
- Soft Deletes: yes
- Audit Columns: yes

#### workflow_logs
- Purpose: complete workflow transition history
- Columns:
  - `id`, `uuid`, `workflow_instance_id`, `step_id`, `actor_id`, `action`, `comment`, `old_status`, `new_status`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_workflow_logs_instance (workflow_instance_id)`, `idx_workflow_logs_step (step_id)`, `idx_workflow_logs_actor (actor_id)`, `idx_workflow_logs_instance_created (workflow_instance_id, created_at)`
- Foreign Keys: `workflow_instance_id -> workflow_instances.id`, `step_id -> workflow_steps.id`, `actor_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `comment`, `actor_id`
- Soft Deletes: yes
- Audit Columns: yes

#### workflow_approvals
- Purpose: step-level approval records
- Columns:
  - `id`, `uuid`, `workflow_instance_id`, `step_id`, `approver_id`, `decision`, `comment`, `approved_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_workflow_approvals_instance (workflow_instance_id)`, `idx_workflow_approvals_step (step_id)`, `idx_workflow_approvals_approver (approver_id)`, `idx_workflow_approvals_instance_step (workflow_instance_id, step_id)`
- Foreign Keys: `workflow_instance_id -> workflow_instances.id`, `step_id -> workflow_steps.id`, `approver_id -> users.id`
- Unique Constraints: `uniq_workflow_approvals_instance_step_approver (workflow_instance_id, step_id, approver_id)`
- Nullable Fields: `comment`, `approved_at`
- Soft Deletes: yes
- Audit Columns: yes

#### workflow_events
- Purpose: workflow emitted events and triggers
- Columns:
  - `id`, `uuid`, `workflow_instance_id`, `event_name`, `payload`, `triggered_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_workflow_events_instance (workflow_instance_id)`, `idx_workflow_events_name (event_name)`
- Foreign Keys: `workflow_instance_id -> workflow_instances.id`
- Unique Constraints: none
- Nullable Fields: `payload`
- Soft Deletes: yes
- Audit Columns: yes

### 5.7 Documents and Files

#### document_types
- Purpose: document category catalog
- Columns:
  - `id`, `uuid`, `name`, `slug`, `description`, `is_required`, `status`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_document_types_slug (slug)`, `idx_document_types_status (status)`
- Foreign Keys: none
- Unique Constraints: `uniq_document_types_slug (slug)`
- Nullable Fields: `description`
- Soft Deletes: yes
- Audit Columns: yes

#### documents
- Purpose: documents attached to users, orders, or KYC
- Columns:
  - `id`, `uuid`, `document_type_id`, `owner_type`, `owner_id`, `title`, `file_path`, `mime_type`, `status`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_documents_type (document_type_id)`, `idx_documents_owner (owner_type, owner_id)`, `idx_documents_status (status)`
- Foreign Keys: `document_type_id -> document_types.id`
- Unique Constraints: none
- Nullable Fields: `file_path`, `verified_at`
- Soft Deletes: yes
- Audit Columns: yes

#### document_approvals
- Purpose: document review approval result
- Columns:
  - `id`, `uuid`, `document_id`, `approver_id`, `decision`, `comment`, `approved_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_document_approvals_document (document_id)`, `idx_document_approvals_approver (approver_id)`, `idx_document_approvals_decision (decision)`
- Foreign Keys: `document_id -> documents.id`, `approver_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `comment`, `approved_at`
- Soft Deletes: yes
- Audit Columns: yes

#### media
- Purpose: centralized media metadata
- Columns:
  - `id`, `uuid`, `model_type`, `model_id`, `collection_name`, `file_name`, `mime_type`, `disk`, `size_bytes`, `path`, `uuid_reference`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_media_model (model_type, model_id)`, `idx_media_collection (collection_name)`, `idx_media_mime (mime_type)`
- Foreign Keys: none
- Unique Constraints: none
- Nullable Fields: `path`
- Soft Deletes: yes
- Audit Columns: yes

#### files
- Purpose: generic file storage metadata
- Columns:
  - `id`, `uuid`, `fileable_type`, `fileable_id`, `storage_path`, `original_name`, `mime_type`, `size_bytes`, `checksum`, `status`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_files_fileable (fileable_type, fileable_id)`, `idx_files_status (status)`, `idx_files_checksum (checksum)`
- Foreign Keys: none
- Unique Constraints: `uniq_files_checksum (checksum)`
- Nullable Fields: `storage_path`
- Soft Deletes: yes
- Audit Columns: yes

### 5.8 Communication and Notifications

#### notifications
- Purpose: in-app and system notifications
- Columns:
  - `id`, `uuid`, `notifiable_type`, `notifiable_id`, `type`, `title`, `message`, `data`, `read_at`, `sent_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_notifications_notifiable (notifiable_type, notifiable_id)`, `idx_notifications_type (type)`, `idx_notifications_read (read_at)`
- Foreign Keys: none
- Unique Constraints: none
- Nullable Fields: `read_at`, `sent_at`, `data`
- Soft Deletes: yes
- Audit Columns: yes

#### sms_logs
- Purpose: SMS and OTP delivery logs
- Columns:
  - `id`, `uuid`, `user_id`, `recipient_phone`, `provider`, `message`, `status`, `response_code`, `sent_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_sms_logs_user (user_id)`, `idx_sms_logs_phone (recipient_phone)`, `idx_sms_logs_status (status)`, `idx_sms_logs_sent_at (sent_at)`
- Foreign Keys: `user_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `user_id`, `response_code`
- Soft Deletes: yes
- Audit Columns: yes

#### chat_rooms
- Purpose: chat conversation containers
- Columns:
  - `id`, `uuid`, `name`, `room_type`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_chat_rooms_type (room_type)`, `idx_chat_rooms_status (status)`, `idx_chat_rooms_created_by (created_by)`
- Foreign Keys: `created_by -> users.id`
- Unique Constraints: none
- Nullable Fields: `name`
- Soft Deletes: yes
- Audit Columns: yes

#### chat_messages
- Purpose: chat messages
- Columns:
  - `id`, `uuid`, `chat_room_id`, `sender_id`, `message`, `message_type`, `status`, `read_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_chat_messages_room (chat_room_id)`, `idx_chat_messages_sender (sender_id)`, `idx_chat_messages_status (status)`, `fulltext idx_chat_messages_message (message)`
- Foreign Keys: `chat_room_id -> chat_rooms.id`, `sender_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `message`
- Soft Deletes: yes
- Audit Columns: yes

#### chat_attachments
- Purpose: file attachments for chat messages
- Columns:
  - `id`, `uuid`, `chat_message_id`, `file_path`, `mime_type`, `size_bytes`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_chat_attachments_message (chat_message_id)`
- Foreign Keys: `chat_message_id -> chat_messages.id`
- Unique Constraints: none
- Nullable Fields: `file_path`
- Soft Deletes: yes
- Audit Columns: yes

### 5.9 Trust, Risk, and Governance

#### ratings
- Purpose: user-to-user ratings
- Columns:
  - `id`, `uuid`, `from_user_id`, `to_user_id`, `order_id`, `score`, `comment`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_ratings_from (from_user_id)`, `idx_ratings_to (to_user_id)`, `idx_ratings_order (order_id)`, `idx_ratings_score (score)`
- Foreign Keys: `from_user_id -> users.id`, `to_user_id -> users.id`, `order_id -> orders.id`
- Unique Constraints: `uniq_ratings_user_order (from_user_id, to_user_id, order_id)`
- Nullable Fields: `comment`
- Soft Deletes: yes
- Audit Columns: yes

#### reviews
- Purpose: review records for products, users, or services
- Columns:
  - `id`, `uuid`, `user_id`, `reviewable_type`, `reviewable_id`, `title`, `body`, `rating`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_reviews_user (user_id)`, `idx_reviews_reviewable (reviewable_type, reviewable_id)`, `idx_reviews_rating (rating)`
- Foreign Keys: `user_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `title`, `body`
- Soft Deletes: yes
- Audit Columns: yes

#### complaints
- Purpose: dispute complaints
- Columns:
  - `id`, `uuid`, `user_id`, `order_id`, `subject`, `body`, `status`, `priority`, `assigned_to`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_complaints_user (user_id)`, `idx_complaints_order (order_id)`, `idx_complaints_status (status)`, `idx_complaints_priority (priority)`, `fulltext idx_complaints_search (subject, body)`
- Foreign Keys: `user_id -> users.id`, `order_id -> orders.id`, `assigned_to -> users.id`
- Unique Constraints: none
- Nullable Fields: `order_id`, `assigned_to`
- Soft Deletes: yes
- Audit Columns: yes

#### complaint_messages
- Purpose: complaint conversation messages
- Columns:
  - `id`, `uuid`, `complaint_id`, `sender_id`, `message`, `is_internal`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_complaint_messages_complaint (complaint_id)`, `idx_complaint_messages_sender (sender_id)`, `idx_complaint_messages_complaint_created (complaint_id, created_at)`
- Foreign Keys: `complaint_id -> complaints.id`, `sender_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `message`
- Soft Deletes: yes
- Audit Columns: yes

#### arbitrations
- Purpose: formal arbitration outcomes
- Columns:
  - `id`, `uuid`, `complaint_id`, `arbitrator_id`, `status`, `decision`, `decision_at`, `notes`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_arbitrations_complaint (complaint_id)`, `idx_arbitrations_arbitrator (arbitrator_id)`, `idx_arbitrations_status (status)`
- Foreign Keys: `complaint_id -> complaints.id`, `arbitrator_id -> users.id`
- Unique Constraints: `uniq_arbitrations_complaint (complaint_id)`
- Nullable Fields: `decision`, `notes`, `decision_at`
- Soft Deletes: yes
- Audit Columns: yes

#### violations
- Purpose: policy violations and sanctions
- Columns:
  - `id`, `uuid`, `user_id`, `violation_type`, `severity`, `description`, `status`, `resolved_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_violations_user (user_id)`, `idx_violations_severity (severity)`, `idx_violations_status (status)`
- Foreign Keys: `user_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `description`, `resolved_at`
- Soft Deletes: yes
- Audit Columns: yes

#### vip_memberships
- Purpose: VIP user subscriptions or memberships
- Columns:
  - `id`, `uuid`, `user_id`, `tier`, `status`, `starts_at`, `ends_at`, `notes`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_vip_user (user_id)`, `idx_vip_status (status)`, `idx_vip_ends_at (ends_at)`
- Foreign Keys: `user_id -> users.id`
- Unique Constraints: `uniq_vip_user (user_id)`
- Nullable Fields: `notes`
- Soft Deletes: yes
- Audit Columns: yes

### 5.10 Reporting and Platform Operations

#### reports
- Purpose: report generation history
- Columns:
  - `id`, `uuid`, `requested_by`, `report_type`, `filters`, `format`, `status`, `file_path`, `generated_at`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_reports_requester (requested_by)`, `idx_reports_type (report_type)`, `idx_reports_status (status)`
- Foreign Keys: `requested_by -> users.id`
- Unique Constraints: none
- Nullable Fields: `file_path`, `generated_at`, `filters`
- Soft Deletes: yes
- Audit Columns: yes

#### activity_logs
- Purpose: user activity log
- Columns:
  - `id`, `uuid`, `user_id`, `action`, `subject_type`, `subject_id`, `ip_address`, `user_agent`, `payload`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_activity_logs_user (user_id)`, `idx_activity_logs_action (action)`, `idx_activity_logs_subject (subject_type, subject_id)`, `idx_activity_logs_created (created_at, action)`
- Foreign Keys: `user_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `payload`, `ip_address`, `user_agent`
- Soft Deletes: yes
- Audit Columns: yes

#### audit_logs
- Purpose: immutable audit trail for critical operations
- Columns:
  - `id`, `uuid`, `user_id`, `action`, `subject_type`, `subject_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_audit_logs_user (user_id)`, `idx_audit_logs_action (action)`, `idx_audit_logs_subject (subject_type, subject_id)`, `idx_audit_logs_created (created_at, action)`
- Foreign Keys: `user_id -> users.id`
- Unique Constraints: none
- Nullable Fields: `old_values`, `new_values`, `ip_address`, `user_agent`
- Soft Deletes: no
- Audit Columns: no

#### system_logs
- Purpose: infrastructure and runtime exception logs
- Columns:
  - `id`, `uuid`, `level`, `channel`, `message`, `context`, `trace_id`, `request_id`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_system_logs_level (level)`, `idx_system_logs_channel (channel)`, `idx_system_logs_trace (trace_id)`, `idx_system_logs_request (request_id)`
- Foreign Keys: none
- Unique Constraints: none
- Nullable Fields: `context`
- Soft Deletes: yes
- Audit Columns: yes

#### settings
- Purpose: configurable business and platform settings
- Columns:
  - `id`, `uuid`, `group_name`, `key`, `value`, `type`, `is_public`, `is_encrypted`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`
- Primary Key: `id`
- UUID: yes
- Indexes: `idx_settings_group (group_name)`, `idx_settings_key (key)`, `idx_settings_group_key (group_name, key)`
- Foreign Keys: none
- Unique Constraints: `uniq_settings_group_key (group_name, key)`
- Nullable Fields: `value`
- Soft Deletes: yes
- Audit Columns: yes

---

## 6. Index Strategy

### Recommended indexes
- Primary keys on `id`
- Unique indexes on business identifiers such as `uuid`, `email`, `phone`, `order_number`, `contract_number`, `escrow_number`, `slug`, `reference_number`
- Composite indexes for frequent filters:
  - `(status, created_at)`
  - `(user_id, status)`
  - `(order_id, created_at)`
  - `(entity_type, entity_id)`
- Full text indexes:
  - `advertisements.title`, `advertisements.description`
  - `chat_messages.message`
  - `complaints.subject`, `complaints.body`
  - `loan_products.name`, `loan_products.description`

### Partitioning strategy for scale
Apply partitioning by month for:
- `audit_logs`
- `activity_logs`
- `wallet_transactions`
- `escrow_transactions`
- `notifications`

---

## 7. Naming Convention

- Tables: lowercase snake_case, plural nouns
  - `users`, `orders`, `wallet_transactions`
- Columns: lowercase snake_case
  - `created_at`, `is_verified`
- Foreign keys: `<table>_id`
  - `user_id`, `bank_id`
- Join tables: `<table_a>_<table_b>`
  - `role_user`, `permission_role`
- Boolean columns: `is_...`, `has_...`
- Enums stored as varchar with constrained values

---

## 8. Foreign Key Strategy

- Use `RESTRICT` for critical links such as orders to advertisements and contracts to orders.
- Use `SET NULL` for optional references such as `reviewer_id`, `assigned_to`, `deleted_by`.
- Avoid hard deletes in financial modules.
- Keep foreign keys indexed for join performance.

---

## 9. UUID Strategy

- Every major table includes a `uuid` column.
- `id` is the internal primary key for efficiency.
- `uuid` is used for external integration, APIs, events, and distributed systems.
- Recommended storage: `char(36)` or `binary(16)` for performance if needed.

---

## 10. Soft Delete Strategy

- Apply soft deletes to all business and operational tables.
- Use `deleted_at` and `deleted_by`.
- Avoid hard deletes for financial, compliance, and access-related records.
- Keep `audit_logs` immutable and not soft-deletable.

---

## 11. Audit Strategy

Every critical record should be auditable with:
- actor user
- IP address
- user agent
- action
- subject entity
- old values
- new values
- timestamp

Recommended audit table:
- `audit_logs`

This supports:
- KYC approvals
- escrow release/refund
- orders and contract signing
- workflow step approvals
- admin/operator actions
- financial movement changes

---

## 12. Enums to Use

Recommended enum-like columns:

- `users.status`: `pending`, `active`, `suspended`, `disabled`
- `otp_requests.status`: `pending`, `used`, `expired`, `failed`
- `kyc_submissions.status`: `draft`, `submitted`, `reviewing`, `approved`, `rejected`, `requires_more_info`
- `advertisements.status`: `draft`, `published`, `paused`, `closed`, `expired`, `rejected`
- `orders.status`: `pending_payment`, `escrowed`, `active`, `completed`, `cancelled`, `disputed`
- `contracts.status`: `draft`, `active`, `signed`, `expired`, `void`
- `wallet_transactions.transaction_type`: `deposit`, `withdrawal`, `fee`, `refund`, `commission`
- `escrow.status`: `pending_funding`, `funded`, `released`, `refunded`, `disputed`, `cancelled`
- `workflow_instances.status`: `pending`, `in_progress`, `waiting_approval`, `approved`, `rejected`, `completed`, `cancelled`
- `complaints.status`: `open`, `reviewing`, `escalated`, `resolved`, `closed`
- `arbitrations.status`: `pending`, `assigned`, `hearing`, `resolved`, `closed`
- `vip_memberships.status`: `active`, `expired`, `suspended`

---

## 13. Future Scalability Notes

This schema is designed for growth to millions of users and can scale further by:
- using read replicas for reporting and analytics
- partitioning large transactional tables by date
- storing large file objects in S3-compatible storage while keeping metadata in MySQL
- caching permissions, hot listings, and common settings in Redis
- moving reporting workloads to separate databases later
- keeping workflow rules data-driven rather than hard-coded in application logic
