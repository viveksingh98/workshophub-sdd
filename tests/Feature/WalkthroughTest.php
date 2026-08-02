<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\HolidayPeriod;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WalkthroughTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function owner(): User
    {
        return User::query()->firstOrFail();
    }

    private function nextDateFor(array $weekdays): string
    {
        for ($i = 1; $i <= 30; $i++) {
            $day = Carbon::today()->addDays($i);
            if (in_array($day->format('l'), $weekdays, true)) {
                return $day->toDateString();
            }
        }

        return Carbon::today()->addDay()->toDateString();
    }

    // ── Auth (Units 35 + 45) ────────────────────────────────────────

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/studio-dashboard')->assertRedirect('/studio-access');
    }

    public function test_login_needs_all_three_fields_to_match(): void
    {
        $wrongPhone = $this->post('/studio-access', [
            'email' => 'hello@workshophub.local',
            'phone' => '0000000000',
            'password' => 'workshop123',
        ]);
        $wrongPhone->assertSessionHasErrors('email');
        $this->assertGuest();

        $right = $this->post('/studio-access', [
            'email' => 'hello@workshophub.local',
            'phone' => '98765 43210',
            'password' => 'workshop123',
        ]);
        $right->assertRedirect('/studio-dashboard');
        $this->assertAuthenticated();
    }

    // ── Availability engine (Units 35 + 45) ─────────────────────────

    public function test_booking_options_follow_the_weekly_grid(): void
    {
        $response = $this->get('/booking/options?mode=online');
        $response->assertStatus(200);

        foreach ($response->json('dates') as $date) {
            $this->assertSame('Monday', Carbon::parse($date)->format('l'));
        }
    }

    public function test_holiday_period_blocks_public_dates(): void
    {
        $blocked = $this->nextDateFor(['Monday']);
        HolidayPeriod::create(['starts_on' => $blocked, 'ends_on' => $blocked]);

        $this->assertNotContains($blocked, $this->get('/booking/options?mode=online')->json('dates'));
    }

    public function test_holiday_mode_pauses_all_booking(): void
    {
        Setting::updateOrCreate(['key' => 'holiday_mode'], ['value' => '1']);

        $this->assertSame([], $this->get('/booking/options?mode=in_studio')->json('dates'));
        $this->assertSame([], $this->get('/booking/options?mode=online')->json('dates'));
    }

    public function test_break_between_sessions_shapes_the_slot_grid(): void
    {
        // 60-minute classes + 15-minute break (defaults) → slots every 75 min
        $date = $this->nextDateFor(['Tuesday', 'Wednesday', 'Thursday', 'Friday']);
        $slots = $this->get('/booking/options?mode=in_studio&date='.$date)->json('slots');

        $this->assertSame('09:00', $slots[0]);
        $this->assertSame('10:15', $slots[1]);
    }

    public function test_taken_slot_disappears_and_cannot_be_double_booked(): void
    {
        $date = $this->nextDateFor(['Tuesday', 'Wednesday', 'Thursday', 'Friday']);

        $this->withSession(['booking_captcha' => [2, 2]])->post('/bookings', [
            'mode' => 'in_studio', 'scheduled_date' => $date, 'starts_at' => '09:00',
            'visitor_name' => 'First Member', 'phone' => '9811100001', 'security_answer' => 4,
        ])->assertSessionDoesntHaveErrors();

        $this->assertNotContains('09:00', $this->get('/booking/options?mode=in_studio&date='.$date)->json('slots'));

        $this->withSession(['booking_captcha' => [2, 2]])->post('/bookings', [
            'mode' => 'in_studio', 'scheduled_date' => $date, 'starts_at' => '09:00',
            'visitor_name' => 'Second Member', 'phone' => '9811100002', 'security_answer' => 4,
        ])->assertSessionHasErrors('starts_at');
    }

    public function test_security_question_blocks_wrong_answers(): void
    {
        $date = $this->nextDateFor(['Tuesday', 'Wednesday', 'Thursday', 'Friday']);

        $this->withSession(['booking_captcha' => [3, 5]])->post('/bookings', [
            'mode' => 'in_studio', 'scheduled_date' => $date, 'starts_at' => '09:00',
            'visitor_name' => 'Bot Visitor', 'phone' => '9811100003', 'security_answer' => 4,
        ])->assertSessionHasErrors('security_answer');
    }

    // ── Dashboard features (Unit 45) ────────────────────────────────

    public function test_owner_can_save_availability_and_holidays(): void
    {
        $this->actingAs($this->owner())->post('/studio-dashboard/availability', [
            'class_duration' => 50, 'online_duration' => 40, 'break_minutes' => 10,
            'advance_days' => 20, 'day_start' => '10:00', 'day_end' => '18:00',
            'week' => ['Monday' => 'online', 'Tuesday' => 'in_studio', 'Wednesday' => 'closed',
                'Thursday' => 'in_studio', 'Friday' => 'in_studio', 'Saturday' => 'closed', 'Sunday' => 'closed'],
        ])->assertRedirect();

        $date = $this->nextDateFor(['Tuesday', 'Thursday', 'Friday']);
        $slots = $this->get('/booking/options?mode=in_studio&date='.$date)->json('slots');
        $this->assertSame(['10:00', '11:00'], array_slice($slots, 0, 2)); // 50 + 10 = every 60
    }

    public function test_session_record_and_waiver_pdf(): void
    {
        $student = Student::query()->firstOrFail();

        $this->actingAs($this->owner())->post("/studio-dashboard/students/{$student->id}/records", [
            'title' => 'Wheel session 1', 'record_date' => now()->toDateString(),
            'content' => '<p>Centered clay for the first time.</p><script>alert(1)</script>',
        ])->assertRedirect();

        $this->assertDatabaseHas('session_records', ['title' => 'Wheel session 1', 'student_id' => $student->id]);
        $this->assertStringNotContainsString('<script>', $student->records()->first()->content);

        $pdf = $this->actingAs($this->owner())->get("/studio-dashboard/students/{$student->id}/waiver");
        $pdf->assertStatus(200)->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->assertStringContainsString($student->name, $pdf->getContent());
    }

    public function test_global_search_finds_students_and_posts(): void
    {
        $response = $this->actingAs($this->owner())->get('/studio-dashboard?section=search&q=Aarav');
        $response->assertStatus(200)->assertSee('Aarav Mehta');
    }

    public function test_theme_activation_and_preview(): void
    {
        $this->actingAs($this->owner())->post('/studio-dashboard/theme', ['theme' => 'night'])->assertRedirect();
        $this->assertSame('night', Setting::map()['theme']);

        $this->get('/theme-preview/paper')->assertStatus(200)->assertSee('themes/paper.css');
        $this->get('/theme-preview/nope')->assertStatus(404);
    }

    public function test_published_post_has_a_public_article_page(): void
    {
        $post = BlogPost::query()->where('status', 'Published')->firstOrFail();

        $this->get('/?view=blog&post='.$post->slug)
            ->assertStatus(200)
            ->assertSee($post->title);
    }

    public function test_manual_booking_search_endpoint_returns_students(): void
    {
        $response = $this->actingAs($this->owner())->get('/studio-dashboard/students/search?q=Aarav');
        $response->assertStatus(200)->assertJsonFragment(['name' => 'Aarav Mehta']);
    }
}
