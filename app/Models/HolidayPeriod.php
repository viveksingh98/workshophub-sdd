<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayPeriod extends Model
{
    protected $fillable = ['starts_on', 'ends_on'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date'];
    }

    public function covers(string $date): bool
    {
        return $this->starts_on->toDateString() <= $date && $this->ends_on->toDateString() >= $date;
    }
}
