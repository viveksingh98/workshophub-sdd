<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'contact', 'archived'];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(StudentNote::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(SessionRecord::class);
    }
}
