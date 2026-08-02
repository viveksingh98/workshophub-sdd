<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    public static function map(): array
    {
        return array_merge(self::defaults(), self::query()->pluck('value', 'key')->all());
    }

    public static function defaults(): array
    {
        return [
            'studio_name' => 'WorkshopHub',
            'owner_name' => 'Maya Rao',
            'logo_text' => 'WH',
            'contact_email' => 'hello@workshophub.local',
            'tagline' => 'Community studio booking',
            'address' => '42 Maker Lane, Central District',
            'hero_message' => 'Discover classes, book seats, and give the studio owner a practical dashboard for classes, bookings, students, content, themes, and settings.',
            'social_links' => '@workshophub',
            'email_subject' => 'Your WorkshopHub booking request',
            'theme' => 'forest',
        ];
    }
}
