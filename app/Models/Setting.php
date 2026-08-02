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
            'theme' => 'studio',
            'contact_phone' => '+91 98765 43210',
            'whatsapp_number' => '+91 98765 43210',
            'meet_the_studio' => 'A community space where makers learn side by side — small groups, real tools, and instructors who love teaching.',
            'pricing_in_studio' => '₹1200 / class',
            'pricing_online' => '₹800 / class',
        ];
    }
}
