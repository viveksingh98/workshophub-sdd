<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionRecord extends Model
{
    protected $fillable = ['student_id', 'title', 'record_date', 'content', 'file_path', 'file_name'];

    protected function casts(): array
    {
        return ['record_date' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
