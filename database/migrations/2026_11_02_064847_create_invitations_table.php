<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('template_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('title', 150);

            $table->string('slug', 150)->unique();

            $table->string('partner_1_name', 100);
            $table->string('partner_2_name', 100);

            $table->text('partner_1_parents')->nullable();
            $table->text('partner_2_parents')->nullable();

            $table->text('quote')->nullable();

            $table->longText('story')->nullable();

            $table->string('status', 20)->default('draft');

            $table->timestamp('published_at')->nullable();

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};