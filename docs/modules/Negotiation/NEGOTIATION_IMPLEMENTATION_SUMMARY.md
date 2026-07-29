# Negotiation Implementation Summary

## Refactor goals
- Preserve existing Negotiation module structure.
- Remove automatic order creation, escrow startup, wallet transactions, and payment side effects.
- Align negotiation lifecycle to canonical statuses.
- Keep legacy fields for compatibility without new business actions.

## What changed
- `Modules/Negotiation/Services/NegotiationService.php`
  - Replaced automatic `convertToOrder()` logic with `completeNegotiation()`.
  - Fired `NegotiationCompleted` instead of `NegotiationConvertedToOrder`.
- `Modules/Negotiation/Services/NegotiationWorkflowService.php`
  - Removed order conversion behavior.
  - Recorded negotiation completion state and `agreed_price`.
- `Modules/Negotiation/Services/NegotiationOfferService.php`
  - Acceptance only updates offer status and logs acceptance.
  - Negotiation completion is delegated to workflow service.
- `Modules/Negotiation/Providers/NegotiationServiceProvider.php`
  - Removed event listeners that triggered order creation, escrow, and advertisement locking.
  - Kept audit logging on negotiation events.
- `Modules/Negotiation/Routes/api.php`
  - Switched negotiation API routes to `/api/v1` to match application convention.
- `Modules/Negotiation/Enums/NegotiationStatus.php`
  - Maintained canonical status values and removed legacy transition use.
- Added `agreed_price` support and new migration for backward compatibility.
- Added `KycAccessService` validation for buyer KYC approval on negotiation creation.

## Runtime behavior
- `OfferAccepted` now signals offer acceptance only.
- `NegotiationCompleted` indicates a final agreement on an offer.
- No module-internal order, escrow, or financial operations are executed automatically.

## Notes
- `order_id` field remains in the negotiation model as a compatibility artifact.
- Future integration should explicitly map a completed negotiation to order creation in a separate controlled process.
