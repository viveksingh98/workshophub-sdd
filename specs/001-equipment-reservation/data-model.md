# Data Model: Equipment Reservation

## equipment

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | e.g. "Pottery Wheel A" |
| slug | string unique | |
| category | string | Ceramics, Media, Fabrication, Textiles |
| usage_note | text | shown to members |
| is_active | boolean | inactive equipment is not offered |
| timestamps | | |

## equipment_reservations

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| reservation_code | string unique | `EQR-####` |
| equipment_id | FK → equipment | cascade on delete |
| student_id | FK → students | reuses the member table |
| member_name | string | denormalized for display |
| contact | string | identity assertion for cancellation |
| reserved_date | date | |
| starts_at / ends_at | time | half-open window `[starts_at, ends_at)` |
| status | string | `active` \| `cancelled` |
| timestamps | | |

Index: `(equipment_id, reserved_date)` — the overlap check always filters on both.

## Invariant

For any equipment + date, no two `active` reservations may satisfy
`a.starts_at < b.ends_at AND a.ends_at > b.starts_at`.
Enforced in `ReservationService::reserve()` inside a transaction holding a
row lock on the equipment row.
