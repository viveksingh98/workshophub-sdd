<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Http\Requests\ReservationRequest;
use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\EquipmentReservation;
use App\Models\Faq;
use App\Models\Instructor;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\WorkshopClass;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WorkshopHubController extends Controller
{
    public function index(Request $request): View
    {
        return view('workshophub.index', $this->viewData($request));
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'passcode' => ['required', 'string', 'min:6'],
        ]);

        $request->session()->put('owner_unlocked', true);
        $request->session()->put('owner_email', $validated['email']);

        return redirect()->route('home', ['view' => 'admin'])->with('status', 'Owner workspace unlocked.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['owner_unlocked', 'owner_email']);

        return redirect()->route('home', ['view' => 'admin'])->with('status', 'Owner workspace locked.');
    }

    public function storeBooking(BookingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $class = WorkshopClass::findOrFail($validated['workshop_class_id']);

        $student = Student::firstOrCreate(
            ['contact' => $validated['contact']],
            ['name' => $validated['visitor_name']]
        );
        $student->update(['name' => $validated['visitor_name']]);

        $booking = Booking::create([
            'booking_code' => $this->nextBookingCode(),
            'workshop_class_id' => $class->id,
            'student_id' => $student->id,
            'visitor_name' => $validated['visitor_name'],
            'contact' => $validated['contact'],
            'scheduled_date' => $validated['scheduled_date'],
            'seats' => $validated['seats'],
            'status' => 'pending',
            'note' => $validated['note'] ?? null,
        ]);

        if (! empty($validated['note'])) {
            StudentNote::create(['student_id' => $student->id, 'note' => $validated['note']]);
        }

        return redirect()
            ->route('home', ['view' => 'booking'])
            ->with('confirmation', "{$booking->booking_code} created for {$class->title} on {$booking->scheduled_date->format('Y-m-d')}.");
    }

    public function storeReservation(ReservationRequest $request, ReservationService $reservations): RedirectResponse
    {
        $reservation = $reservations->reserve($request->validated());

        return redirect()
            ->route('home', ['view' => 'equipment'])
            ->with('confirmation', "{$reservation->reservation_code} — {$reservation->equipment->name} reserved on {$reservation->reserved_date->format('Y-m-d')} from {$reservation->starts_at} to {$reservation->ends_at}.");
    }

    public function cancelReservation(Request $request, EquipmentReservation $reservation, ReservationService $reservations): RedirectResponse
    {
        $validated = $request->validate(['cancel_contact' => ['required', 'string', 'max:160']]);
        $reservations->cancel($reservation, $validated['cancel_contact']);

        return redirect()
            ->route('home', ['view' => 'equipment'])
            ->with('status', "{$reservation->reservation_code} cancelled.");
    }

    public function updateBookingStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:pending,approved,waitlist,cancelled']]);
        $booking->update(['status' => $validated['status']]);

        return redirect()->route('home', ['view' => 'admin'])->with('status', "{$booking->booking_code} marked {$validated['status']}.");
    }

    public function storeClass(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'category' => ['required', 'string', 'max:80'],
            'instructor_name' => ['required', 'string', 'max:120'],
            'weekday' => ['required', 'string', 'max:20'],
            'time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:30', 'max:240'],
            'capacity' => ['required', 'integer', 'min:1', 'max:40'],
            'room' => ['required', 'string', 'max:120'],
            'level' => ['required', 'string', 'max:80'],
            'summary' => ['required', 'string', 'max:500'],
        ]);

        $instructor = Instructor::firstOrCreate(
            ['name' => $validated['instructor_name']],
            [
                'bio' => 'Instructor profile created from the owner dashboard.',
                'expertise' => $validated['category'],
                'image_label' => Str::upper(Str::substr($validated['instructor_name'], 0, 2)),
            ]
        );

        WorkshopClass::create([
            'instructor_id' => $instructor->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(4)),
            'category' => $validated['category'],
            'weekday' => $validated['weekday'],
            'time' => $validated['time'],
            'duration_minutes' => $validated['duration_minutes'],
            'capacity' => $validated['capacity'],
            'room' => $validated['room'],
            'level' => $validated['level'],
            'summary' => $validated['summary'],
            'is_active' => true,
        ]);

        return redirect()->route('home', ['view' => 'admin'])->with('status', 'Class added.');
    }

    public function storeStudentNote(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate(['note' => ['required', 'string', 'max:500']]);
        $student->notes()->create($validated);

        return redirect()->route('home', ['view' => 'admin'])->with('status', 'Student note added.');
    }

    public function storePost(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'excerpt' => ['required', 'string', 'max:500'],
            'status' => ['required', 'in:Draft,Published'],
        ]);

        BlogPost::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(4)),
            'excerpt' => $validated['excerpt'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('home', ['view' => 'admin'])->with('status', 'Blog post saved.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'studio_name' => ['required', 'string', 'max:100'],
            'owner_name' => ['required', 'string', 'max:100'],
            'logo_text' => ['required', 'string', 'max:3'],
            'contact_email' => ['required', 'email', 'max:160'],
            'tagline' => ['required', 'string', 'max:140'],
            'address' => ['required', 'string', 'max:180'],
            'hero_message' => ['required', 'string', 'max:500'],
            'social_links' => ['required', 'string', 'max:160'],
            'email_subject' => ['required', 'string', 'max:160'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->route('home', ['view' => 'admin'])->with('status', 'Settings saved.');
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate(['theme' => ['required', 'in:forest,harbor,clay,ink']]);
        Setting::updateOrCreate(['key' => 'theme'], ['value' => $validated['theme']]);

        return redirect()->route('home', ['view' => 'admin'])->with('status', 'Theme changed.');
    }

    public function waiver(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function (): void {
            echo implode(PHP_EOL, [
                'WorkshopHub Waiver Note',
                '',
                'Participants acknowledge studio safety rules before tool-based classes.',
                'Emergency contact and accessibility notes should be captured before attendance.',
                'This downloadable document maps to Unit 37 support-module requirements.',
            ]);
        }, 'workshophub-waiver-note.txt');
    }

    private function viewData(Request $request): array
    {
        $settings = Setting::map();
        $classes = WorkshopClass::query()->with(['instructor', 'bookings'])->where('is_active', true)->orderBy('weekday')->orderBy('time')->get();
        $bookings = Booking::query()->with(['workshopClass.instructor', 'student'])->latest()->get();
        $students = Student::query()->with(['bookings.workshopClass', 'notes'])->latest()->get();

        $reservationQuery = EquipmentReservation::query()->with('equipment')->orderBy('reserved_date')->orderBy('starts_at');
        if ($request->filled('equipment_filter')) {
            $reservationQuery->where('equipment_id', $request->query('equipment_filter'));
        }
        if ($request->filled('date_filter')) {
            $reservationQuery->whereDate('reserved_date', $request->query('date_filter'));
        }

        return [
            'view' => $request->query('view', 'public'),
            'settings' => $settings,
            'classes' => $classes,
            'categories' => $classes->pluck('category')->unique()->values(),
            'instructors' => Instructor::query()->with('classes')->orderBy('name')->get(),
            'bookings' => $bookings,
            'students' => $students,
            'posts' => BlogPost::query()->latest()->get(),
            'faqs' => Faq::query()->orderBy('sort_order')->get(),
            'equipment' => Equipment::query()->where('is_active', true)->orderBy('name')->get(),
            'reservations' => $reservationQuery->get(),
            'reservationFilters' => [
                'equipment' => $request->query('equipment_filter', ''),
                'date' => $request->query('date_filter', ''),
            ],
            'metrics' => [
                'classes' => $classes->count(),
                'openSeats' => $classes->sum(fn (WorkshopClass $class) => $class->seatsLeft()),
                'bookings' => $bookings->where('status', '!=', 'cancelled')->count(),
                'students' => $students->count(),
                'pending' => $bookings->where('status', 'pending')->count(),
                'approvedSeats' => $bookings->where('status', 'approved')->sum('seats'),
            ],
            'ownerUnlocked' => $request->session()->get('owner_unlocked', false),
            'themes' => ['forest' => 'Forest', 'harbor' => 'Harbor', 'clay' => 'Clay', 'ink' => 'Ink'],
        ];
    }

    private function nextBookingCode(): string
    {
        $last = Booking::query()->latest('id')->value('id') ?? 0;

        return 'BKG-'.str_pad((string) ($last + 1001), 4, '0', STR_PAD_LEFT);
    }
}
