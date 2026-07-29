# Negotiation Architecture

## Purpose
The Negotiation module is responsible for managing buyer-seller negotiation workflows on published advertisements.
It is intentionally decoupled from payment, order creation, escrow, and wallet subsystems.

## Core concepts
- Negotiation: a negotiation instance between a buyer and a seller for a specific advertisement.
- Offer: a price proposal made within a negotiation.
- NegotiationHistory: audit trail events for negotiation lifecycle actions.
- NegotiationStatus: canonical negotiation lifecycle states.

## Business boundaries
- This module only records negotiation events and agreement state.
- It does not create orders, reserve funds, or interact with escrow/payment services.
- Existing legacy fields such as `order_id` remain for historical compatibility only.

## Workflow
1. Buyer initiates a negotiation for a published advertisement.
2. Buyer creates an offer.
3. Seller accepts, rejects, or counters the offer.
4. When the seller accepts an offer:
   - the offer is marked `Accepted`
   - the negotiation is marked `Accepted`
   - `selected_offer_id` and `agreed_price` are recorded
   - `accepted_at` and `closed_at` timestamps are set
   - `NegotiationCompleted` is fired
5. The final negotiation result is exposed via APIs and resources.

## Validation rules
- A buyer cannot negotiate on their own advertisement.
- The advertisement must be published and not archived, sold, or deleted.
- The buyer must have approved KYC before starting negotiation.
- Only a single active negotiation is allowed per buyer and advertisement.
- Only the buyer can create offers.
- Only the seller can accept an offer.

## Extension points
- `OfferAccepted` and `NegotiationCompleted` events may be observed by other modules.
- `CreateOrderListener`, `LockAdvertisementListener`, and `StartEscrowWorkflowListener` are no longer wired into the active negotiation accept flow.

## Data model
- `negotiations` table: holds negotiation state, participants, and selected offer/agreed price.
- `negotiation_offers` table: holds submitted offers with statuses and expiration.
- `negotiation_histories` table: audit log of negotiation events.
