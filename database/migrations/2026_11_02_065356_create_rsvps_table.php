<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invitation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('invitation_guest_id')
                ->nullable()
                ->unique()
                ->constrained('invitation_guests')
                ->nullOnDelete();

            $table->string('guest_name', 150);

            $table->string('attendance_status', 30)
                ->default('pending');

            $table->unsignedSmallInteger('attendee_count')->default(1);

            $table->text('message')->nullable();

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            $table->index(['invitation_id', 'attendance_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsvps');
    }
};