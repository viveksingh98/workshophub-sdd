<?php

namespace App\Http\Controllers;

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
use App\Services\AvailabilityService;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkshopHubController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (\App\Models\User::query()->count() === 0) {
            return redirect()->route('setup');
        }

        return view('workshophub.index', $this->viewData($request));
    }

    /**
     * Unit 44: the public booking flow — mode → open days → free slot,
     * name + phone + note, a security question, and rate limiting.
     */
    public function storeBooking(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:in_studio,online'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'starts_at' => ['required', 'date_format:H:i'],
            'visitor_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'note' => ['nullable', 'string', 'max:500'],
            'security_answer' => ['required', 'integer'],
        ]);

        $captcha = $request->session()->get('booking_captcha');
        if (! is_array($captcha) || (int) $validated['security_answer'] !== $captcha[0] + $captcha[1]) {
            return back()->withErrors(['security_answer' => 'The security answer is wrong — try the little sum again.'])->withInput();
        }

        // Unit 39: the phone is cleaned programmatically and used as the
        // student identifier — spaces and separators never survive.
        $phone = preg_replace('/\D+/', '', $validated['phone']);
        if (strlen($phone) < 10) {
            return back()->withErrors(['phone' => 'The phone number needs at least 10 digits.'])->withInput();
        }

        $booking = DB::transaction(function () use ($validated, $phone) {
            $slotFree = in_array(
                $validated['starts_at'],
                $this->availability->slotsFor($validated['mode'], $validated['scheduled_date']),
                true
            );

            if (! $slotFree) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'starts_at' => 'That slot was just taken or is not open — pick another one.',
                ]);
            }

            $student = Student::firstOrCreate(['contact' => $phone], ['name' => $validated['visitor_name']]);
            $student->update(['name' => $validated['visitor_name']]);

            return Booking::create([
                'booking_code' => $this->nextBookingCode(),
                'mode' => $validated['mode'],
                'student_id' => $student->id,
                'visitor_name' => $validated['visitor_name'],
                'contact' => $phone,
                'scheduled_date' => $validated['scheduled_date'],
                'starts_at' => $validated['starts_at'],
                'seats' => 1,
                'status' => 'pending',
                'note' => $validated['note'] ?? null,
            ]);
        });

        if (! empty($validated['note'])) {
            StudentNote::create(['student_id' => $booking->student_id, 'note' => $validated['note']]);
        }

        DashboardController::notifyNewBooking($booking);
        $request->session()->forget('booking_captcha');

        return redirect()
            ->route('home', ['view' => 'booking'])
            ->with('confirmation', "{$booking->booking_code} confirmed for {$booking->scheduled_date->format('Y-m-d')} at {$booking->starts_at}.")
            ->with('gcal_url', $this->googleCalendarUrl($booking));
    }

    public function bookingOptions(Request $request): JsonResponse
    {
        $mode = $request->query('mode', 'in_studio');
        $date = $request->query('date');

        if (! array_key_exists($mode, AvailabilityService::MODES)) {
            return response()->json(['dates' => [], 'slots' => []]);
        }

        return response()->json([
            'dates' => $this->availability->openDates($mode),
            'slots' => $date ? $this->availability->slotsFor($mode, $date) : [],
        ]);
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

    public function themePreview(Request $request, string $theme): View
    {
        abort_unless(in_array($theme, ['studio', 'garden', 'chalk', 'night', 'paper'], true), 404);

        $data = $this->viewData($request);
        $data['settings']['theme'] = $theme;
        $data['previewingTheme'] = $theme;

        return view('workshophub.index', $data);
    }

    private function viewData(Request $request): array
    {
        $settings = Setting::map();
        $view = $request->query('view', 'public');

        $classes = WorkshopClass::query()->with(['instructor', 'bookings'])->where('is_active', true)->orderBy('weekday')->orderBy('time')->get();
        $publishedPosts = BlogPost::query()->where('status', 'Published')->orderByDesc('published_at')->get();

        $reservationQuery = EquipmentReservation::query()->with('equipment')->orderBy('reserved_date')->orderBy('starts_at');
        if ($request->filled('equipment_filter')) {
            $reservationQuery->where('equipment_id', $request->query('equipment_filter'));
        }
        if ($request->filled('date_filter')) {
            $reservationQuery->whereDate('reserved_date', $request->query('date_filter'));
        }

        if ($view === 'booking') {
            $request->session()->put('booking_captcha', [random_int(2, 9), random_int(2, 9)]);
        }

        return [
            'view' => $view,
            'settings' => $settings,
            'classes' => $classes,
            'categories' => $classes->pluck('category')->unique()->values(),
            'instructors' => Instructor::query()->with('classes')->orderBy('name')->get(),
            'posts' => $publishedPosts,
            'blogCategories' => $publishedPosts->pluck('category')->unique()->values(),
            'activePost' => $request->query('post') ? $publishedPosts->firstWhere('slug', $request->query('post')) : null,
            'faqs' => Faq::query()->orderBy('sort_order')->get(),
            'equipment' => Equipment::query()->where('is_active', true)->orderBy('name')->get(),
            'reservations' => $reservationQuery->get(),
            'reservationFilters' => [
                'equipment' => $request->query('equipment_filter', ''),
                'date' => $request->query('date_filter', ''),
            ],
            'modes' => AvailabilityService::MODES,
            'openDates' => [
                'in_studio' => $this->availability->openDates('in_studio'),
                'online' => $this->availability->openDates('online'),
            ],
            'captcha' => $request->session()->get('booking_captcha'),
            'metrics' => [
                'classes' => $classes->count(),
                'instructors' => Instructor::query()->count(),
                'posts' => $publishedPosts->count(),
                'faqs' => Faq::query()->count(),
            ],
        ];
    }

    private function googleCalendarUrl(Booking $booking): string
    {
        $settings = Setting::map();
        $duration = (int) (json_decode($settings['availability'] ?? '', true)[$booking->mode === 'online' ? 'online_duration' : 'class_duration'] ?? 60);

        $start = \Carbon\Carbon::parse($booking->scheduled_date->toDateString().' '.$booking->starts_at);
        $end = $start->copy()->addMinutes($duration);
        $format = 'Ymd\THis';

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => $settings['studio_name'].' — '.AvailabilityService::MODES[$booking->mode].' class',
            'dates' => $start->format($format).'/'.$end->format($format),
            'details' => 'Booking '.$booking->booking_code,
            'location' => $booking->mode === 'online' ? 'Online' : $settings['address'],
        ]);
    }

    private function nextBookingCode(): string
    {
        $last = Booking::query()->latest('id')->value('id') ?? 0;

        return 'BKG-'.str_pad((string) ($last + 1001), 4, '0', STR_PAD_LEFT);
    }
}
