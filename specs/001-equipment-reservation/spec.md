# Feature Specification: Equipment Reservation

**Feature**: `001-equipment-reservation`
**Status**: Implemented
**Input**: "Members can reserve a piece of studio equipment for a time window. Overlapping reservations for the same equipment must be rejected. Only the member who created a reservation can cancel it. Staff can list reservations for a given equipment and day."

## User Scenarios & Testing

### User Story 1 — Reserve equipment (Priority: P1)

A member selects a piece of equipment (e.g. the pottery wheel or the camera kit), a date, and a start/end time, and submits the reservation. If the equipment is free for that window, the reservation is confirmed with a reservation code. If any active reservation overlaps the window, the request is rejected with a clear reason.

**Why this priority**: without the ability to reserve, no other functionality has value.

**Acceptance criteria**
- A free window produces an active reservation and a visible confirmation.
- An overlapping window on the same equipment and day is rejected; no reservation is created.
- The same window on *different* equipment is allowed.
- Back-to-back windows (one ends exactly when the next starts) do not count as overlapping.
- A member profile is created from the contact if it does not exist yet.

### User Story 2 — Cancel a reservation (Priority: P2)

Only the member who created a reservation can cancel it. Identity is asserted with the contact used at reservation time. A cancelled slot becomes reservable again.

**Acceptance criteria**
- Cancellation with a non-matching contact is rejected and the reservation stays active.
- Cancellation with the matching contact marks the reservation cancelled.
- A new reservation over a cancelled window succeeds.

### User Story 3 — List reservations (Priority: P3)

Staff can list reservations filtered by equipment and by day.

**Acceptance criteria**
- The list can be filtered by equipment, by day, or both.
- Reservations outside the filter do not appear.

## Success Criteria

- **SC-001**: A member can complete a reservation in one form submission.
- **SC-002**: Zero double-bookings — under concurrent submissions for the same window, exactly one reservation succeeds.
- **SC-003**: Rejected requests always explain why.

## Out of Scope

- Payments, pricing, or deposits
- Recurring reservations (tracked as a future change request)
- Notifications

> Note: no framework, storage, or route decisions appear in this file — per the constitution, technology enters at plan time.
