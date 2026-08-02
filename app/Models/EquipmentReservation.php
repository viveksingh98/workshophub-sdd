<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_code',
        'equipment_id',
        'student_id',
        'member_name',
        'contact',
        'reserved_date',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return ['reserved_date' => 'date'];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
