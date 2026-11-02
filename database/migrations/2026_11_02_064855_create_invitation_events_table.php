<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invitation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('event_type', 30);

            $table->string('title', 100);

            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();

            $table->string('timezone', 50)->default('Asia/Jakarta');

            $table->string('venue_name', 150)->nullable();

            $table->text('address')->nullable();

            $table->text('maps_url')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamps();

            $table->index(['invitation_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_events');
    }
};