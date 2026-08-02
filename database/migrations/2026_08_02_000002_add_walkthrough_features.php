<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 32)->nullable()->after('email');
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->boolean('archived')->default(false)->after('contact');
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->longText('content')->nullable()->after('excerpt');
            $table->string('image_path')->nullable()->after('content');
            $table->string('category')->default('Studio Life')->after('image_path');
            $table->date('published_at')->nullable()->after('category');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('mode', 20)->default('in_studio')->after('booking_code');
            $table->time('starts_at')->nullable()->after('scheduled_date');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->unsignedBigInteger('workshop_class_id')->nullable()->change();
        });

        Schema::create('holiday_periods', function (Blueprint $table): void {
            $table->id();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamps();
        });

        Schema::create('personal_events', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->date('event_date');
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('session_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('record_date');
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_records');
        Schema::dropIfExists('personal_events');
        Schema::dropIfExists('holiday_periods');

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['mode', 'starts_at']);
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropColumn(['content', 'image_path', 'category', 'published_at']);
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->dropColumn('archived');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('phone');
        });
    }
};
