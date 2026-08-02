<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentReservation;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /**
     * Create a reservation with the overlap guard from the spec:
     * the check and the insert run inside one transaction while the
     * equipment row is locked, so two concurrent requests for the same
     * slot cannot both succeed (FR-002 / SC-002 in the spec).
     */
    public function reserve(array $data): EquipmentReservation
    {
        return DB::transaction(function () use ($data): EquipmentReservation {
            $equipment = Equipment::query()
                ->whereKey($data['equipment_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $overlaps = $equipment->reservations()
                ->where('status', 'active')
                ->whereDate('reserved_date', $data['reserved_date'])
                ->where('starts_at', '<', $data['ends_at'])
                ->where('ends_at', '>', $data['starts_at'])
                ->exists();

            if ($overlaps) {
                throw ValidationException::withMessages([
                    'starts_at' => "{$equipment->name} is already reserved for an overlapping time window on that day.",
                ]);
            }

            $student = Student::firstOrCreate(
                ['contact' => $data['contact']],
                ['name' => $data['member_name']]
            );

            return EquipmentReservation::create([
                'reservation_code' => $this->nextReservationCode(),
                'equipment_id' => $equipment->id,
                'student_id' => $student->id,
                'member_name' => $data['member_name'],
                'contact' => $data['contact'],
                'reserved_date' => $data['reserved_date'],
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'status' => 'active',
            ]);
        });
    }

    /**
     * Only the member who created the reservation can cancel it (FR-003).
     * Identity is asserted with the contact used at reservation time.
     */
    public function cancel(EquipmentReservation $reservation, string $contact): void
    {
        if ($reservation->contact !== trim($contact)) {
            throw ValidationException::withMessages([
                'cancel_contact' => 'Only the member who created this reservation can cancel it — the contact does not match.',
            ]);
        }

        $reservation->update(['status' => 'cancelled']);
    }

    private function nextReservationCode(): string
    {
        $last = EquipmentReservation::query()->latest('id')->value('id') ?? 0;

        return 'EQR-'.str_pad((string) ($last + 2001), 4, '0', STR_PAD_LEFT);
    }
}
