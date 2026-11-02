<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invitation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('media_asset_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('role', 30);

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_primary')->default(false);

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique([
                'invitation_id',
                'media_asset_id',
                'role'
            ]);

            $table->index(['invitation_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_media');
    }
};