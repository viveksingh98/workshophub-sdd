<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'title',
        'slug',
        'category',
        'weekday',
        'time',
        'duration_minutes',
        'capacity',
        'room',
        'level',
        'summary',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'capacity' => 'integer',
            'duration_minutes' => 'integer',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function bookedSeats(?string $date = null): int
    {
        return (int) $this->bookings()
            ->whereNotIn('status', ['cancelled'])
            ->when($date, fn ($query) => $query->whereDate('scheduled_date', $date))
            ->sum('seats');
    }

    public function seatsLeft(?string $date = null): int
    {
        return max(0, $this->capacity - $this->bookedSeats($date));
    }
}
