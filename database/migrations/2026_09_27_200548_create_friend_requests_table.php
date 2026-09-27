<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('friend_requests', function (Blueprint $table) {
        $table->id();
// means sender_id must correspond to an actual user.
        $table->foreignId('sender_id')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->foreignId('receiver_id')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->string('status')->default('pending');

        $table->timestamp('responded_at')->nullable();

        $table->timestamps();

        $table->index(['receiver_id', 'status']);
        $table->index(['sender_id', 'status']);
    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::dropIfExists('friend_requests');
}
};