<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Faq;
use App\Models\HolidayPeriod;
use App\Models\PersonalEvent;
use App\Models\SessionRecord;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\WorkshopClass;
use App\Services\AvailabilityService;
use App\Services\WaiverPdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability)
    {
    }

    public function index(Request $request): View
    {
        $section = $request->query('section', 'home');
        $settings = Setting::map();

        $data = [
            'section' => $section,
            'settings' => $settings,
            'themes' => ['studio' => 'Studio', 'garden' => 'Garden', 'chalk' => 'Chalk', 'night' => 'Night', 'paper' => 'Paper'],
            'modes' => AvailabilityService::MODES,
        ];

        $data += match ($section) {
            'availability' => [
                'config' => $this->availability->config(),
                'holidayMode' => $this->availability->holidayMode(),
                'holidays' => HolidayPeriod::query()->orderBy('starts_on')->get(),
            ],
            'bookings' => [
                'bookings' => Booking::query()->with(['workshopClass', 'student'])
                    ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
                    ->orderByDesc('scheduled_date')->orderBy('starts_at')->get(),
                'students' => Student::query()->where('archived', false)->orderBy('name')->get(),
                'config' => $this->availability->config(),
            ],
            'calendar' => $this->calendarData($request),
            'students' => [
                'students' => Student::query()->with(['bookings', 'records'])
                    ->when(! $request->boolean('archived'), fn ($q) => $q->where('archived', false))
                    ->orderBy('name')->get(),
                'showArchived' => $request->boolean('archived'),
            ],
            'student' => [
                'student' => Student::query()->with(['bookings.workshopClass', 'notes', 'records'])->findOrFail($request->query('id')),
            ],
            'blog' => [
                'posts' => BlogPost::query()->latest()->get(),
                'categories' => ['Studio Life', 'Techniques', 'Announcements', 'Student Stories'],
                'editPost' => $request->query('edit') ? BlogPost::query()->find($request->query('edit')) : null,
            ],
            'faqs' => ['faqs' => Faq::query()->orderBy('sort_order')->get()],
            'web' => ['faqs' => Faq::query()->orderBy('sort_order')->get()],
            'search' => $this->searchData($request),
            default => $this->homeData(),
        };

        return view('workshophub.dashboard.index', $data);
    }

    private function homeData(): array
    {
        $today = Carbon::today()->toDateString();

        return [
            'metrics' => [
                'upcoming' => Booking::query()->where('status', '!=', 'cancelled')->whereDate('scheduled_date', '>=', $today)->count(),
                'students' => Student::query()->where('archived', false)->count(),
                'articles' => BlogPost::query()->count(),
                'sessions' => SessionRecord::query()->count(),
            ],
            'todaysBookings' => Booking::query()->with('workshopClass')
                ->whereDate('scheduled_date', $today)->where('status', '!=', 'cancelled')
                ->orderBy('starts_at')->get(),
            'todaysEvents' => PersonalEvent::query()->whereDate('event_date', $today)->orderBy('starts_at')->get(),
        ];
    }

    private function calendarData(Request $request): array
    {
        $view = $request->query('range', 'month');
        $anchor = Carbon::parse($request->query('date', Carbon::today()->toDateString()));

        [$from, $to] = match ($view) {
            'day' => [$anchor->copy(), $anchor->copy()],
            'week' => [$anchor->copy()->startOfWeek(), $anchor->copy()->endOfWeek()],
            default => [$anchor->copy()->startOfMonth()->startOfWeek(), $anchor->copy()->endOfMonth()->endOfWeek()],
        };

        return [
            'range' => $view,
            'anchor' => $anchor,
            'from' => $from,
            'to' => $to,
            'calendarBookings' => Booking::query()->with('workshopClass')
                ->whereBetween('scheduled_date', [$from->toDateString(), $to->toDateString()])
                ->where('status', '!=', 'cancelled')->get()->groupBy(fn (Booking $b) => $b->scheduled_date->toDateString()),
            'calendarEvents' => PersonalEvent::query()
                ->whereBetween('event_date', [$from->toDateString(), $to->toDateString()])
                ->get()->groupBy(fn (PersonalEvent $e) => $e->event_date->toDateString()),
        ];
    }

    private function searchData(Request $request): array
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return ['query' => '', 'results' => []];
        }

        $like = '%'.$q.'%';

        return [
            'query' => $q,
            'results' => [
                'Students' => Student::query()->where('name', 'like', $like)->orWhere('contact', 'like', $like)->limit(10)->get()
                    ->map(fn ($s) => ['label' => $s->name.' · '.$s->contact, 'url' => route('dashboard', ['section' => 'student', 'id' => $s->id])]),
                'Bookings' => Booking::query()->where('visitor_name', 'like', $like)->orWhere('booking_code', 'like', $like)->limit(10)->get()
                    ->map(fn ($b) => ['label' => $b->booking_code.' · '.$b->visitor_name.' · '.$b->scheduled_date->toDateString(), 'url' => route('dashboard', ['section' => 'bookings'])]),
                'Blog posts' => BlogPost::query()->where('title', 'like', $like)->limit(10)->get()
                    ->map(fn ($p) => ['label' => $p->title, 'url' => route('dashboard', ['section' => 'blog', 'edit' => $p->id])]),
                'Session records' => SessionRecord::query()->where('title', 'like', $like)->with('student')->limit(10)->get()
                    ->map(fn ($r) => ['label' => $r->title.' · '.$r->student->name, 'url' => route('dashboard', ['section' => 'student', 'id' => $r->student_id])]),
                'FAQs' => Faq::query()->where('question', 'like', $like)->limit(10)->get()
                    ->map(fn ($f) => ['label' => $f->question, 'url' => route('dashboard', ['section' => 'faqs'])]),
            ],
        ];
    }

    // ── Availability ────────────────────────────────────────────────

    public function saveAvailability(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_duration' => ['required', 'integer', 'min:15', 'max:240'],
            'online_duration' => ['required', 'integer', 'min:15', 'max:240'],
            'break_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'advance_days' => ['required', 'integer', 'min:1', 'max:120'],
            'day_start' => ['required', 'date_format:H:i'],
            'day_end' => ['required', 'date_format:H:i', 'after:day_start'],
            'week' => ['required', 'array'],
            'week.*' => ['in:in_studio,online,closed'],
        ]);

        $this->availability->save($validated);
        Setting::updateOrCreate(['key' => 'holiday_mode'], ['value' => $request->boolean('holiday_mode') ? '1' : '0']);

        return redirect()->route('dashboard', ['section' => 'availability'])
            ->with('status', 'Availability saved — the public booking calendar now follows this grid.');
    }

    public function storeHoliday(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ]);

        HolidayPeriod::create($validated);

        return redirect()->route('dashboard', ['section' => 'availability'])
            ->with('status', 'Holiday period added — those days are now blocked on the public calendar.');
    }

    public function deleteHoliday(HolidayPeriod $holiday): RedirectResponse
    {
        $holiday->delete();

        return redirect()->route('dashboard', ['section' => 'availability'])->with('status', 'Holiday period removed.');
    }

    // ── Bookings ────────────────────────────────────────────────────

    public function storeBooking(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'visitor_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'mode' => ['required', 'in:in_studio,online'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'starts_at' => ['required', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $phone = preg_replace('/\D+/', '', $validated['phone']);
        if (strlen($phone) < 10) {
            return back()->withErrors(['phone' => 'The phone number needs at least 10 digits.'])->withInput();
        }

        $student = Student::firstOrCreate(['contact' => $phone], ['name' => $validated['visitor_name']]);
        $student->update(['name' => $validated['visitor_name'], 'archived' => false]);

        Booking::create([
            'booking_code' => $this->nextBookingCode(),
            'mode' => $validated['mode'],
            'student_id' => $student->id,
            'visitor_name' => $validated['visitor_name'],
            'contact' => $phone,
            'scheduled_date' => $validated['scheduled_date'],
            'starts_at' => $validated['starts_at'],
            'seats' => 1,
            'status' => 'approved',
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('dashboard', ['section' => 'bookings'])
            ->with('status', 'Booking created manually — the student profile was matched or created by phone number.');
    }

    public function updateBooking(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,waitlist,cancelled'],
            'scheduled_date' => ['nullable', 'date'],
            'starts_at' => ['nullable', 'date_format:H:i'],
        ]);

        $booking->update(array_filter($validated, fn ($value) => $value !== null));

        return redirect()->route('dashboard', ['section' => 'bookings'])
            ->with('status', "{$booking->booking_code} updated.");
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        return response()->json(
            Student::query()->where('archived', false)
                ->where(fn ($query) => $query->where('name', 'like', "%$q%")->orWhere('contact', 'like', "%$q%"))
                ->limit(8)->get(['id', 'name', 'contact'])
        );
    }

    // ── Calendar ────────────────────────────────────────────────────

    public function storeEvent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'event_date' => ['required', 'date'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i', 'after:starts_at'],
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        PersonalEvent::create($validated);

        return redirect()->route('dashboard', ['section' => 'calendar', 'date' => $validated['event_date']])
            ->with('status', 'Personal event added — it does not consume booking slots.');
    }

    public function deleteEvent(PersonalEvent $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('dashboard', ['section' => 'calendar'])->with('status', 'Personal event removed.');
    }

    // ── Students + records ──────────────────────────────────────────

    public function updateStudent(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'contact' => ['required', 'string', 'max:160'],
            'archived' => ['nullable', 'boolean'],
        ]);

        $student->update([
            'name' => $validated['name'],
            'contact' => $validated['contact'],
            'archived' => $request->boolean('archived'),
        ]);

        return redirect()->route('dashboard', ['section' => 'student', 'id' => $student->id])
            ->with('status', 'Student profile saved.');
    }

    public function storeNote(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate(['note' => ['required', 'string', 'max:500']]);
        StudentNote::create(['student_id' => $student->id, 'note' => $validated['note']]);

        return redirect()->route('dashboard', ['section' => 'student', 'id' => $student->id])->with('status', 'Note added.');
    }

    public function storeRecord(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'record_date' => ['required', 'date'],
            'content' => ['nullable', 'string', 'max:20000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:8192'],
        ]);

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = $file->getClientOriginalName();
            $filePath = 'uploads/records/'.Str::random(12).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/records'), basename($filePath));
        }

        SessionRecord::create([
            'student_id' => $student->id,
            'title' => $validated['title'],
            'record_date' => $validated['record_date'],
            'content' => $this->cleanRichText($validated['content'] ?? ''),
            'file_path' => $filePath,
            'file_name' => $fileName,
        ]);

        return redirect()->route('dashboard', ['section' => 'student', 'id' => $student->id])
            ->with('status', 'Session record saved to the student history.');
    }

    public function waiver(Student $student, WaiverPdf $pdf): Response
    {
        return response($pdf->forStudent($student), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="waiver-'.Str::slug($student->name).'.pdf"',
        ]);
    }

    public function waiverBlank(WaiverPdf $pdf): Response
    {
        return response($pdf->blank(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="waiver-template.pdf"',
        ]);
    }

    // ── Blog ────────────────────────────────────────────────────────

    public function storePost(Request $request): RedirectResponse
    {
        $validated = $this->validatePost($request);

        BlogPost::create($validated + ['slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(4))]);

        return redirect()->route('dashboard', ['section' => 'blog'])->with('status', 'Post saved.');
    }

    public function updatePost(Request $request, BlogPost $post): RedirectResponse
    {
        $post->update($this->validatePost($request));

        return redirect()->route('dashboard', ['section' => 'blog'])->with('status', 'Post updated.');
    }

    public function deletePost(BlogPost $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('dashboard', ['section' => 'blog'])->with('status', 'Post deleted.');
    }

    private function validatePost(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'excerpt' => ['required', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:60000'],
            'category' => ['required', 'string', 'max:60'],
            'status' => ['required', 'in:Draft,Published'],
            'published_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $validated['content'] = $this->cleanRichText($validated['content'] ?? '');
        $validated['published_at'] = $validated['published_at'] ?? now()->toDateString();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $validated['image_path'] = 'uploads/blog/'.Str::random(12).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/blog'), basename($validated['image_path']));
        }

        unset($validated['image']);

        return $validated;
    }

    // ── FAQs ────────────────────────────────────────────────────────

    public function storeFaq(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:200'],
            'answer' => ['required', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Faq::create($validated + ['sort_order' => $validated['sort_order'] ?? Faq::query()->max('sort_order') + 1]);

        return redirect()->route('dashboard', ['section' => 'faqs'])->with('status', 'FAQ added.');
    }

    public function deleteFaq(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('dashboard', ['section' => 'faqs'])->with('status', 'FAQ removed.');
    }

    // ── Web management ──────────────────────────────────────────────

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'studio_name' => ['required', 'string', 'max:100'],
            'owner_name' => ['required', 'string', 'max:100'],
            'logo_text' => ['required', 'string', 'max:3'],
            'contact_email' => ['required', 'email', 'max:160'],
            'contact_phone' => ['nullable', 'string', 'max:32'],
            'whatsapp_number' => ['nullable', 'string', 'max:32'],
            'tagline' => ['required', 'string', 'max:140'],
            'address' => ['required', 'string', 'max:180'],
            'hero_message' => ['required', 'string', 'max:500'],
            'meet_the_studio' => ['nullable', 'string', 'max:2000'],
            'social_instagram' => ['nullable', 'string', 'max:200'],
            'social_youtube' => ['nullable', 'string', 'max:200'],
            'social_facebook' => ['nullable', 'string', 'max:200'],
            'gmail_username' => ['nullable', 'email', 'max:160'],
            'gmail_app_password' => ['nullable', 'string', 'max:64'],
            'notify_email' => ['nullable', 'email', 'max:160'],
            'waiver_template' => ['nullable', 'string', 'max:8000'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return redirect()->route('dashboard', ['section' => 'web'])->with('status', 'Web settings saved.');
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate(['theme' => ['required', 'in:studio,garden,chalk,night,paper']]);
        Setting::updateOrCreate(['key' => 'theme'], ['value' => $validated['theme']]);

        return redirect()->route('dashboard', ['section' => 'web'])->with('status', Str::title($validated['theme']).' theme activated on the public site.');
    }

    public function uploadImage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slot' => ['required', 'in:hero_image,logo_image,favicon'],
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $file = $request->file('image');
        $path = 'uploads/site/'.$validated['slot'].'.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads/site'), basename($path));

        Setting::updateOrCreate(['key' => $validated['slot']], ['value' => $path.'?v='.time()]);

        return redirect()->route('dashboard', ['section' => 'web'])
            ->with('status', 'Image saved — it now overrides the default on the public site.');
    }

    // ── Shared helpers ──────────────────────────────────────────────

    public static function notifyNewBooking(Booking $booking): void
    {
        $settings = Setting::map();
        if (empty($settings['gmail_username']) || empty($settings['gmail_app_password'])) {
            return;
        }

        rescue(function () use ($booking, $settings): void {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => 'smtp.gmail.com',
                'mail.mailers.smtp.port' => 587,
                'mail.mailers.smtp.username' => $settings['gmail_username'],
                'mail.mailers.smtp.password' => $settings['gmail_app_password'],
                'mail.from.address' => $settings['gmail_username'],
                'mail.from.name' => $settings['studio_name'],
            ]);

            Mail::raw(
                "New booking {$booking->booking_code}: {$booking->visitor_name} · {$booking->mode} · {$booking->scheduled_date->toDateString()} {$booking->starts_at}",
                fn ($message) => $message->to($settings['notify_email'] ?: $settings['gmail_username'])
                    ->subject($settings['email_subject'] ?? 'New WorkshopHub booking')
            );
        }, report: false);
    }

    private function cleanRichText(string $html): string
    {
        return strip_tags($html, '<p><br><b><strong><i><em><u><h2><h3><ul><ol><li><a><blockquote>');
    }

    private function nextBookingCode(): string
    {
        $last = Booking::query()->latest('id')->value('id') ?? 0;

        return 'BKG-'.str_pad((string) ($last + 1001), 4, '0', STR_PAD_LEFT);
    }
}
