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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('notes')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('price');
            $table->string('status')->default('pending'); // pending, confirmed, completed, cancelled
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->index(['date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
