<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_guests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invitation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('guest_name', 150);

            $table->string('phone', 30)->nullable();

            $table->string('email', 150)->nullable();

            $table->string('invitation_token', 64)->unique();

            $table->unsignedSmallInteger('max_attendees')->default(1);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['invitation_id', 'guest_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_guests');
    }
};