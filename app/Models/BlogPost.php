<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'status', 'excerpt', 'content', 'image_path', 'category', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'date'];
    }
}
