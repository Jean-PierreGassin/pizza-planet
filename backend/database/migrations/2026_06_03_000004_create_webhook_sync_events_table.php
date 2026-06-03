<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_sync_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_status_event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('order_status_event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->string('destination_url');
            $table->json('payload');
            $table->string('status');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->timestamps();

            $table->index('order_item_status_event_id');
            $table->index('order_status_event_id');
            $table->index(['status', 'last_attempted_at']);
            $table->unique(['order_item_status_event_id', 'event_type']);
            $table->unique(['order_status_event_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_sync_events');
    }
};
