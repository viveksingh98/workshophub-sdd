# Implementation Plan: Equipment Reservation

**Spec**: [spec.md](./spec.md)
**Input constraints**: Laravel + MySQL, server-rendered Blade, native JS. Overlap prevention must run inside a DB transaction with row locks — not only an application-level check.

## Summary

Add an `Equipment` catalog and `EquipmentReservation` records to the existing WorkshopHub monolith. The overlap guard runs in `ReservationService::reserve()`: the equipment row is locked with `lockForUpdate()` inside `DB::transaction()`, the overlap check runs against `active` reservations, and the insert happens before the lock is released — so two concurrent requests for the same window serialize, and exactly one succeeds (SC-002).

## Technical Context

- **Language/Framework**: PHP / Laravel (existing app conventions)
- **Storage**: MySQL in production; SQLite in-memory for tests. Overlap window test: `starts_at < new.ends_at AND ends_at > new.starts_at` on the same equipment + date, status `active`.
- **Locking**: `SELECT ... FOR UPDATE` on the equipment row serializes reservation attempts per equipment. (MySQL has no exclusion constraints, so the transaction + row lock carries the invariant.)
- **UI**: new `equipment` view tab on the single-page Blade shell; forms post to named routes.
- **Identity**: reuse the `students` table; the member's contact is the identity assertion for cancellation (matches the booking flow convention).
- **Testing**: feature tests per acceptance criterion, seeded data, dynamic dates.

## Decisions

| Decision | Choice | Why |
|---|---|---|
| Overlap enforcement | Transaction + row lock in a service | MySQL lacks exclusion constraints; app-level check alone is racy |
| Cancellation auth | Contact match | No login system for members in this demo; consistent with bookings |
| Half-open windows | `[starts_at, ends_at)` | Back-to-back slots must not collide |
| Cancelled rows | Keep with `status=cancelled` | Audit trail; frees the window because guard filters on `active` |

See [data-model.md](./data-model.md), [contracts/http.md](./contracts/http.md), and [research.md](./research.md).
