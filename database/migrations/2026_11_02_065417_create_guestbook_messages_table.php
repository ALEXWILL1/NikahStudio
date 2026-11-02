<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guestbook_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invitation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('invitation_guest_id')
                ->nullable()
                ->constrained('invitation_guests')
                ->nullOnDelete();

            $table->string('sender_name', 150);

            $table->text('message');

            $table->string('status', 20)->default('pending');

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['invitation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guestbook_messages');
    }
};