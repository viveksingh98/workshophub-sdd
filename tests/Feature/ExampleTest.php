<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
            ->assertSee('Book a class');
    }

    public function test_booking_request_creates_booking_and_student(): void
    {
        $date = $this->nextOpenInStudioDate();

        $response = $this->withSession(['booking_captcha' => [3, 4]])->post('/bookings', [
            'mode' => 'in_studio',
            'scheduled_date' => $date,
            'starts_at' => '09:00',
            'visitor_name' => 'Demo Viewer',
            'phone' => '+91 98111 22233',
            'note' => 'Feature test booking',
            'security_answer' => 7,
        ]);

        $response->assertRedirect('/?view=booking');
        $this->assertDatabaseHas(Booking::class, ['visitor_name' => 'Demo Viewer', 'mode' => 'in_studio']);
        // Unit 39: the phone is cleaned and becomes the student identifier
        $this->assertDatabaseHas(Student::class, ['contact' => '919811122233']);
    }

    private function nextOpenInStudioDate(): string
    {
        for ($i = 1; $i <= 30; $i++) {
            $day = Carbon::today()->addDays($i);
            if (in_array($day->format('l'), ['Tuesday', 'Wednesday', 'Thursday', 'Friday'], true)) {
                return $day->toDateString();
            }
        }

        return Carbon::today()->addDay()->toDateString();
    }
}
