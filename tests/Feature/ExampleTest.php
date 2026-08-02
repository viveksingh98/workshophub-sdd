<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Student;
use App\Models\WorkshopClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_public_workshophub_page_returns_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('WorkshopHub')
            ->assertSee('Ceramic Basics')
            ->assertSee('Book a demo seat');
    }

    public function test_booking_request_creates_booking_and_student(): void
    {
        $class = WorkshopClass::query()->firstOrFail();

        $response = $this->post('/bookings', [
            'workshop_class_id' => $class->id,
            'visitor_name' => 'Demo Viewer',
            'contact' => 'demo.viewer@example.com',
            'scheduled_date' => now()->addDays(10)->toDateString(),
            'seats' => 1,
            'note' => 'Feature test booking',
        ]);

        $response->assertRedirect('/?view=booking');
        $this->assertDatabaseHas(Booking::class, ['visitor_name' => 'Demo Viewer']);
        $this->assertDatabaseHas(Student::class, ['contact' => 'demo.viewer@example.com']);
    }
}
