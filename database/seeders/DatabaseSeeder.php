<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\Faq;
use App\Models\Instructor;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\User;
use App\Models\WorkshopClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'hello@workshophub.local'],
            [
                'name' => 'Maya Rao',
                'phone' => '9876543210',
                'password' => 'workshop123',
            ]
        );

        foreach ([
            'studio_name' => 'WorkshopHub',
            'owner_name' => 'Maya Rao',
            'logo_text' => 'WH',
            'contact_email' => 'hello@workshophub.local',
            'tagline' => 'Community studio booking',
            'address' => '42 Maker Lane, Central District',
            'hero_message' => 'Discover classes, book seats, and give the studio owner a practical dashboard for classes, bookings, students, content, themes, and settings.',
            'social_links' => '@workshophub',
            'email_subject' => 'Your WorkshopHub booking request',
            'theme' => 'studio',
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $instructors = collect([
            ['name' => 'Nina Kapoor', 'expertise' => 'Ceramics', 'image_label' => 'NK', 'bio' => 'Ceramic artist focused on friendly first-time studio sessions.'],
            ['name' => 'Jon Bell', 'expertise' => 'Painting', 'image_label' => 'JB', 'bio' => 'Watercolor instructor who keeps color theory practical.'],
            ['name' => 'Priya Nair', 'expertise' => 'Woodcraft', 'image_label' => 'PN', 'bio' => 'Woodcraft teacher with a strong safety-first workshop rhythm.'],
            ['name' => 'Eli Morgan', 'expertise' => 'Writing', 'image_label' => 'EM', 'bio' => 'Writing facilitator for small peer-feedback circles.'],
        ])->mapWithKeys(fn (array $data) => [
            $data['name'] => Instructor::updateOrCreate(['name' => $data['name']], $data),
        ]);

        $classModels = collect([
            ['title' => 'Ceramic Basics', 'category' => 'Ceramics', 'instructor' => 'Nina Kapoor', 'weekday' => 'Monday', 'time' => '18:00', 'duration_minutes' => 90, 'capacity' => 8, 'room' => 'Clay Studio', 'level' => 'Beginner', 'summary' => 'Hands-on wheel and hand-building session for first-time makers.'],
            ['title' => 'Watercolor Lab', 'category' => 'Painting', 'instructor' => 'Jon Bell', 'weekday' => 'Wednesday', 'time' => '19:30', 'duration_minutes' => 75, 'capacity' => 10, 'room' => 'North Light Room', 'level' => 'Mixed', 'summary' => 'Color washes, layering, and small finished studies in one relaxed class.'],
            ['title' => 'Weekend Woodcraft', 'category' => 'Woodcraft', 'instructor' => 'Priya Nair', 'weekday' => 'Saturday', 'time' => '10:00', 'duration_minutes' => 120, 'capacity' => 6, 'room' => 'Bench Room', 'level' => 'Intermediate', 'summary' => 'Build a simple shelf while learning tool safety and finishing basics.'],
            ['title' => 'Creative Writing Circle', 'category' => 'Writing', 'instructor' => 'Eli Morgan', 'weekday' => 'Sunday', 'time' => '16:00', 'duration_minutes' => 60, 'capacity' => 12, 'room' => 'Quiet Room', 'level' => 'All levels', 'summary' => 'Prompt-led writing, peer feedback, and a weekly take-home exercise.'],
        ])->mapWithKeys(function (array $data) use ($instructors) {
            $title = $data['title'];
            $instructor = $instructors[$data['instructor']];
            unset($data['instructor']);

            return [
                $title => WorkshopClass::updateOrCreate(
                    ['slug' => Str::slug($title)],
                    array_merge($data, ['instructor_id' => $instructor->id, 'is_active' => true])
                ),
            ];
        });

        foreach ([
            ['code' => 'BKG-1001', 'class' => 'Ceramic Basics', 'name' => 'Aarav Mehta', 'contact' => 'aarav@example.com', 'date' => '2026-07-15', 'seats' => 2, 'status' => 'approved', 'note' => 'First class visit.'],
            ['code' => 'BKG-1002', 'class' => 'Watercolor Lab', 'name' => 'Sara Williams', 'contact' => 'sara@example.com', 'date' => '2026-07-15', 'seats' => 1, 'status' => 'pending', 'note' => 'Needs left-handed setup if available.'],
            ['code' => 'BKG-1003', 'class' => 'Weekend Woodcraft', 'name' => 'Kenji Sato', 'contact' => 'kenji@example.com', 'date' => '2026-07-18', 'seats' => 1, 'status' => 'approved', 'note' => 'Has basic tool experience.'],
        ] as $data) {
            $student = Student::updateOrCreate(['contact' => $data['contact']], ['name' => $data['name']]);
            $booking = Booking::updateOrCreate(
                ['booking_code' => $data['code']],
                [
                    'workshop_class_id' => $classModels[$data['class']]->id,
                    'student_id' => $student->id,
                    'visitor_name' => $data['name'],
                    'contact' => $data['contact'],
                    'scheduled_date' => $data['date'],
                    'seats' => $data['seats'],
                    'status' => $data['status'],
                    'note' => $data['note'],
                ]
            );
            StudentNote::firstOrCreate(['student_id' => $student->id, 'note' => $booking->note]);
        }

        foreach ([
            ['title' => 'How to choose your first class', 'status' => 'Published', 'category' => 'Studio Life', 'excerpt' => 'Start with the material you want to touch, then choose a pace that leaves room for questions.', 'content' => '<p>Start with the material you want to touch — clay, paint, wood, or words. Then choose a pace that leaves room for questions.</p><h2>Beginner friendly</h2><p>Every class marked Beginner assumes zero experience. Tools and materials are provided.</p>'],
            ['title' => 'What to bring to a workshop', 'status' => 'Published', 'category' => 'Techniques', 'excerpt' => 'Comfortable clothes, curiosity, and any accessibility notes the instructor should know.', 'content' => '<p>Comfortable clothes you do not mind getting messy, curiosity, and any accessibility notes the instructor should know about in advance.</p>'],
            ['title' => 'New evening slots this season', 'status' => 'Draft', 'category' => 'Announcements', 'excerpt' => 'We are testing later evening classes for people who work full time.', 'content' => '<p>We are testing later evening classes for people who work full time — tell us which days work for you.</p>'],
        ] as $post) {
            BlogPost::updateOrCreate(['slug' => Str::slug($post['title'])], $post + ['published_at' => now()->toDateString()]);
        }

        foreach ([
            ['name' => 'Pottery Wheel A', 'category' => 'Ceramics', 'usage_note' => 'Wipe down the wheel head and return tools to the rack after use.'],
            ['name' => 'Camera Kit', 'category' => 'Media', 'usage_note' => 'Check battery charge and format the memory card before your slot.'],
            ['name' => 'Laser Cutter', 'category' => 'Fabrication', 'usage_note' => 'Approved materials only — the list is posted next to the machine.'],
            ['name' => 'Sewing Machine 2', 'category' => 'Textiles', 'usage_note' => 'Bring your own fabric; studio thread is provided.'],
        ] as $item) {
            Equipment::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                array_merge($item, ['is_active' => true])
            );
        }

        foreach ([
            ['question' => 'Can beginners join?', 'answer' => 'Yes. Beginner-friendly classes are marked on each class card.', 'sort_order' => 1],
            ['question' => 'Do visitors pay in this demo?', 'answer' => 'No. The spec says not to build unrelated payment features unless requested.', 'sort_order' => 2],
            ['question' => 'What happens after booking?', 'answer' => 'The booking is created, capacity is checked, and a student profile is created or updated.', 'sort_order' => 3],
        ] as $faq) {
            Faq::updateOrCreate(['question' => $faq['question']], $faq);
        }
    }
}
