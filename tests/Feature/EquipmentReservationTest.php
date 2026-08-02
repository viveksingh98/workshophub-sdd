<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\EquipmentReservation;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentReservationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function reservationPayload(array $overrides = []): array
    {
        return array_merge([
            'equipment_id' => Equipment::query()->firstOrFail()->id,
            'member_name' => 'Demo Member',
            'contact' => 'demo.member@example.com',
            'reserved_date' => now()->addDays(14)->toDateString(),
            'starts_at' => '10:00',
            'ends_at' => '11:00',
        ], $overrides);
    }

    public function test_member_can_reserve_a_free_time_window(): void
    {
        $response = $this->post('/reservations', $this->reservationPayload());

        $response->assertRedirect('/?view=equipment');
        $this->assertDatabaseHas(EquipmentReservation::class, ['member_name' => 'Demo Member', 'status' => 'active']);
        $this->assertDatabaseHas(Student::class, ['contact' => 'demo.member@example.com']);
    }

    public function test_overlapping_reservation_for_same_equipment_is_rejected(): void
    {
        $this->post('/reservations', $this->reservationPayload());

        $response = $this->post('/reservations', $this->reservationPayload([
            'member_name' => 'Second Member',
            'contact' => 'second.member@example.com',
            'starts_at' => '10:30',
            'ends_at' => '11:30',
        ]));

        $response->assertSessionHasErrors('starts_at');
        $this->assertSame(1, EquipmentReservation::query()->count());
    }

    public function test_same_time_window_on_different_equipment_is_allowed(): void
    {
        [$first, $second] = Equipment::query()->limit(2)->get();

        $this->post('/reservations', $this->reservationPayload(['equipment_id' => $first->id]));
        $this->post('/reservations', $this->reservationPayload([
            'equipment_id' => $second->id,
            'contact' => 'second.member@example.com',
        ]));

        $this->assertSame(2, EquipmentReservation::query()->where('status', 'active')->count());
    }

    public function test_back_to_back_windows_do_not_count_as_overlap(): void
    {
        $this->post('/reservations', $this->reservationPayload());

        $response = $this->post('/reservations', $this->reservationPayload([
            'contact' => 'second.member@example.com',
            'starts_at' => '11:00',
            'ends_at' => '12:00',
        ]));

        $response->assertSessionDoesntHaveErrors();
        $this->assertSame(2, EquipmentReservation::query()->count());
    }

    public function test_only_the_creator_can_cancel_a_reservation(): void
    {
        $this->post('/reservations', $this->reservationPayload());
        $reservation = EquipmentReservation::query()->firstOrFail();

        $blocked = $this->post("/reservations/{$reservation->id}/cancel", ['cancel_contact' => 'someone.else@example.com']);
        $blocked->assertSessionHasErrors('cancel_contact');
        $this->assertSame('active', $reservation->fresh()->status);

        $allowed = $this->post("/reservations/{$reservation->id}/cancel", ['cancel_contact' => 'demo.member@example.com']);
        $allowed->assertRedirect('/?view=equipment');
        $this->assertSame('cancelled', $reservation->fresh()->status);
    }

    public function test_cancelled_slot_can_be_reserved_again(): void
    {
        $this->post('/reservations', $this->reservationPayload());
        $reservation = EquipmentReservation::query()->firstOrFail();
        $this->post("/reservations/{$reservation->id}/cancel", ['cancel_contact' => 'demo.member@example.com']);

        $response = $this->post('/reservations', $this->reservationPayload([
            'contact' => 'second.member@example.com',
        ]));

        $response->assertSessionDoesntHaveErrors();
        $this->assertSame(1, EquipmentReservation::query()->where('status', 'active')->count());
    }

    public function test_reservation_list_filters_by_equipment_and_day(): void
    {
        [$first, $second] = Equipment::query()->limit(2)->get();
        $this->post('/reservations', $this->reservationPayload(['equipment_id' => $first->id]));
        $this->post('/reservations', $this->reservationPayload([
            'equipment_id' => $second->id,
            'contact' => 'second.member@example.com',
            'reserved_date' => now()->addDays(15)->toDateString(),
        ]));
        $this->get('/?view=equipment'); // consume the confirmation flash before asserting

        $response = $this->get('/?view=equipment&equipment_filter='.$first->id.'&date_filter='.now()->addDays(14)->toDateString());

        $response->assertStatus(200)
            ->assertSee($first->name)
            ->assertSee('EQR-2001')
            ->assertDontSee('EQR-2002');
    }
}
