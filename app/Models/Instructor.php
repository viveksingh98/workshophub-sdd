<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instructor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'bio', 'expertise', 'image_label'];

    public function classes(): HasMany
    {
        return $this->hasMany(WorkshopClass::class);
    }
}
