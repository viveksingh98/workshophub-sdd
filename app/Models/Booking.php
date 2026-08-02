<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'mode',
        'workshop_class_id',
        'student_id',
        'visitor_name',
        'contact',
        'scheduled_date',
        'starts_at',
        'seats',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'seats' => 'integer',
        ];
    }

    public function workshopClass(): BelongsTo
    {
        return $this->belongsTo(WorkshopClass::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
