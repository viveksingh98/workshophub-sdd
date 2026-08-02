# Tasks: Equipment Reservation

**Input**: [spec.md](./spec.md) · [plan.md](./plan.md)
**Format**: `[ID] [Story] Description — file path`

## Phase 1: Data layer

- [x] T001 [US1] Migration: `equipment` + `equipment_reservations` tables with `(equipment_id, reserved_date)` index — `database/migrations/2026_08_02_000001_create_equipment_reservation_tables.php`
- [x] T002 [US1] `Equipment` model with reservations relation — `app/Models/Equipment.php`
- [x] T003 [US1] `EquipmentReservation` model with equipment/student relations — `app/Models/EquipmentReservation.php`
- [x] T004 [US1] Seed demo equipment (pottery wheel, camera kit, laser cutter, sewing machine) — `database/seeders/DatabaseSeeder.php`

## Phase 2: Domain rules

- [x] T005 [US1] `ReservationService::reserve()` — transaction + `lockForUpdate()` + overlap check + insert — `app/Services/ReservationService.php`
- [x] T006 [US2] `ReservationService::cancel()` — creator-only via contact match — `app/Services/ReservationService.php`
- [x] T007 [US1] `ReservationRequest` validation (date today+, `ends_at` after `starts_at`, contact format) — `app/Http/Requests/ReservationRequest.php`

## Phase 3: HTTP + UI

- [x] T008 [US1] `storeReservation` + `cancelReservation` controller actions — `app/Http/Controllers/WorkshopHubController.php`
- [x] T009 [US1] Routes `POST /reservations`, `POST /reservations/{reservation}/cancel` — `routes/web.php`
- [x] T010 [US3] Equipment view tab: reserve form + filterable list + cancel form — `resources/views/workshophub/partials/equipment.blade.php`, `index.blade.php`
- [x] T011 [US3] Equipment/day filters wired through `viewData()` — `app/Http/Controllers/WorkshopHubController.php`

## Phase 4: Tests (one per acceptance criterion)

- [x] T012 [US1] Free window reserves; member profile created — `tests/Feature/EquipmentReservationTest.php`
- [x] T013 [US1] Overlap on same equipment rejected; count stays 1
- [x] T014 [US1] Same window, different equipment allowed
- [x] T015 [US1] Back-to-back windows allowed
- [x] T016 [US2] Creator-only cancel (wrong contact rejected, right contact cancels)
- [x] T017 [US2] Cancelled window reservable again
- [x] T018 [US3] List filters by equipment + day
