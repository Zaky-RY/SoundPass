<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_tiers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('concert_id')->constrained('concerts')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedInteger('total_quota');
            $table->unsignedInteger('remaining_quota');
            $table->timestamps();
        });

        // Terapkan constraint CHECK (remaining_quota >= 0)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE ticket_tiers ADD CONSTRAINT check_remaining_quota CHECK (remaining_quota >= 0)');
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::unprepared('
                CREATE TRIGGER check_ticket_tier_remaining_quota_insert
                BEFORE INSERT ON ticket_tiers
                FOR EACH ROW
                WHEN NEW.remaining_quota < 0
                BEGIN
                    SELECT RAISE(ABORT, "CHECK constraint failed: remaining_quota >= 0");
                END;
            ');

            DB::unprepared('
                CREATE TRIGGER check_ticket_tier_remaining_quota_update
                BEFORE UPDATE ON ticket_tiers
                FOR EACH ROW
                WHEN NEW.remaining_quota < 0
                BEGIN
                    SELECT RAISE(ABORT, "CHECK constraint failed: remaining_quota >= 0");
                END;
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_tiers');
    }
};
