<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalEvent extends Model
{
    protected $fillable = ['title', 'event_date', 'starts_at', 'ends_at', 'note'];

    protected function casts(): array
    {
        return ['event_date' => 'date'];
    }
}
