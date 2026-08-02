# Research: Equipment Reservation

Why each notable choice was made.

## Overlap prevention: transaction + row lock (not a DB constraint)

PostgreSQL offers `EXCLUDE USING GIST` on range columns, which enforces
no-overlap at the schema level. **MySQL has no exclusion constraints**, and this
project's constitution fixes MySQL as the production store. The equivalent
guarantee is achieved by serializing reservation attempts per equipment:

1. `DB::transaction()` opens the unit of work.
2. `Equipment::whereKey(...)->lockForUpdate()->firstOrFail()` takes a row lock —
   any concurrent reservation attempt for the same equipment blocks here.
3. The overlap query runs against `active` reservations only.
4. The insert commits before the lock is released.

Two concurrent requests for the same window therefore cannot both pass the
check — the second waits, re-reads, sees the first insert, and is rejected (SC-002).

## Half-open time windows

Windows are `[starts_at, ends_at)`. The overlap predicate
`starts_at < new.ends_at AND ends_at > new.starts_at` deliberately uses strict
inequalities so back-to-back slots (11:00 end, 11:00 start) never collide —
studios chain sessions.

## Cancellation identity: contact match

The demo has no member login. The booking flow already treats the contact as
the member identity, so cancellation asserts the same contact. A wrong contact
is a validation error, not a 403 — consistent with the app's flash-based UX.

## Keep cancelled rows

`status = cancelled` instead of deleting: the list view can show history, and
the overlap guard filters on `active`, so the window frees automatically.
