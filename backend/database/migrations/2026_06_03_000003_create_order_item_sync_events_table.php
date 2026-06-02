<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_sync_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_status_event_id')->constrained()->cascadeOnDelete();
            $table->string('destination_url');
            $table->json('payload');
            $table->string('status');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->timestamps();

            $table->index('item_status_event_id');
            $table->index(['status', 'last_attempted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_sync_events');
    }
};
