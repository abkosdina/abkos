# Negotiation Module

This module provides a reusable negotiation workflow for loan marketplace advertisements. It is intentionally independent of the chat subsystem and only stores an optional conversation reference when needed.

## Included components
- Models: Negotiation, NegotiationOffer, NegotiationHistory
- Services: NegotiationService, NegotiationOfferService, NegotiationValidationService, NegotiationWorkflowService
- Repositories and interfaces
- DTOs, actions, controllers, requests, resources, policies, events, listeners
- Migration for negotiations, negotiation_offers, and negotiation_histories

## Current negotiated flow
This implementation is refactored to support negotiation and offer agreement without automatically creating orders, reserving funds, or starting escrow workflows.

Important design constraints:
- Negotiation acceptance marks the negotiation as completed and records the selected offer.
- The module does not create deals, orders, wallet transactions, escrow, or payment service calls.
- Order-related fields remain available only for backward compatibility and must not be used for automatic financial side effects.
