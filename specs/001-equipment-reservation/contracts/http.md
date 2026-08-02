# HTTP Contracts: Equipment Reservation

## POST /reservations

Create a reservation.

**Body**
| Field | Rules |
|---|---|
| equipment_id | required, must exist |
| member_name | required, max 120 |
| contact | required, email or ≥10-digit phone |
| reserved_date | required, today or later |
| starts_at | required, `H:i` |
| ends_at | required, `H:i`, after `starts_at` |

**Responses**
- `302 → /?view=equipment` with `confirmation` flash on success
- `302` back with `starts_at` error when the window overlaps an active reservation
- `302` back with field errors on validation failure

## POST /reservations/{reservation}/cancel

Cancel a reservation — creator only.

**Body**
| Field | Rules |
|---|---|
| cancel_contact | required; must match the reservation's contact |

**Responses**
- `302 → /?view=equipment` with `status` flash on success
- `302` back with `cancel_contact` error when the contact does not match

## GET /?view=equipment

Reservation list. Optional query filters: `equipment_filter` (id), `date_filter` (Y-m-d).
