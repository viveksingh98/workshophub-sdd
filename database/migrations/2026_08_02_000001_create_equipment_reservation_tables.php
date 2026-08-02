<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->text('usage_note');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('equipment_reservations', function (Blueprint $table): void {
            $table->id();
            $table->string('reservation_code')->unique();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('member_name');
            $table->string('contact');
            $table->date('reserved_date');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['equipment_id', 'reserved_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_reservations');
        Schema::dropIfExists('equipment');
    }
};
