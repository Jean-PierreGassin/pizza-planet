<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_status_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->timestamps();

            $table->index(['order_item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_status_events');
    }
};
