<?php

declare(strict_types=1);

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
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('concert_id')->constrained('concerts')->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('identity_number', 16);
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->unsignedInteger('subtotal_amount');
            $table->unsignedInteger('tax_amount');
            $table->unsignedInteger('admin_fee');
            $table->unsignedInteger('total_amount');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
