<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\HolidayPeriod;
use App\Models\Setting;
use Carbon\Carbon;

class AvailabilityService
{
    public const MODES = ['in_studio' => 'In-studio', 'online' => 'Online'];

    /**
     * Availability config lives in the settings table as JSON (Unit 35:
     * durations, advance days, day window, weekly grid, holiday mode).
     */
    public function config(): array
    {
        $defaults = [
            'class_duration' => 60,
            'online_duration' => 45,
            'break_minutes' => 15,
            'advance_days' => 30,
            'day_start' => '09:00',
            'day_end' => '19:00',
            'week' => [
                'Monday' => 'online',
                'Tuesday' => 'in_studio',
                'Wednesday' => 'in_studio',
                'Thursday' => 'in_studio',
                'Friday' => 'in_studio',
                'Saturday' => 'closed',
                'Sunday' => 'closed',
            ],
        ];

        $stored = json_decode(Setting::map()['availability'] ?? '', true);

        if (! is_array($stored)) {
            return $defaults;
        }

        $merged = array_merge($defaults, $stored);
        $merged['week'] = array_merge($defaults['week'], $stored['week'] ?? []);

        return $merged;
    }

    public function save(array $config): void
    {
        Setting::updateOrCreate(['key' => 'availability'], ['value' => json_encode($config)]);
    }

    public function holidayMode(): bool
    {
        return (Setting::map()['holiday_mode'] ?? '0') === '1';
    }

    /**
     * Dates a visitor can book for a mode: within the advance window, on a
     * weekday the owner opened for that mode, outside holiday periods —
     * and nothing at all while holiday mode is on (Unit 45).
     */
    public function openDates(string $mode): array
    {
        if ($this->holidayMode()) {
            return [];
        }

        $config = $this->config();
        $holidays = HolidayPeriod::all();
        $dates = [];

        for ($i = 1; $i <= (int) $config['advance_days']; $i++) {
            $day = Carbon::today()->addDays($i);
            $date = $day->toDateString();

            if (($config['week'][$day->format('l')] ?? 'closed') !== $mode) {
                continue;
            }
            if ($holidays->first(fn (HolidayPeriod $period) => $period->covers($date))) {
                continue;
            }

            $dates[] = $date;
        }

        return $dates;
    }

    /**
     * Free slots for a mode + date. Slot length = duration + break (the
     * break-between-sessions math from Unit 35); booked slots drop out.
     * Personal events deliberately do NOT consume slots (Unit 45).
     */
    public function slotsFor(string $mode, string $date): array
    {
        if (! in_array($date, $this->openDates($mode), true)) {
            return [];
        }

        $config = $this->config();
        $duration = (int) ($mode === 'online' ? $config['online_duration'] : $config['class_duration']);
        $step = $duration + (int) $config['break_minutes'];

        $cursor = Carbon::parse("$date {$config['day_start']}");
        $end = Carbon::parse("$date {$config['day_end']}");

        $taken = Booking::query()
            ->whereDate('scheduled_date', $date)
            ->where('mode', $mode)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('starts_at')
            ->pluck('starts_at')
            ->map(fn ($time) => substr((string) $time, 0, 5))
            ->all();

        $slots = [];
        while ($cursor->copy()->addMinutes($duration)->lte($end)) {
            $slot = $cursor->format('H:i');
            if (! in_array($slot, $taken, true)) {
                $slots[] = $slot;
            }
            $cursor->addMinutes($step);
        }

        return $slots;
    }
}
