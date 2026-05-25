<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->constrained('users')->restrictOnDelete();
            $table->foreignId('barber_id')
                ->constrained('users')->restrictOnDelete();
            $table->foreignId('receptionist_id')
                ->nullable()
                ->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'pending',
                'confirmed',
                'in_progress',
                'completed',
                'cancelled',
                'no_show'
            ])->default('pending');
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('notes')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['barber_id', 'appointment_date']);
            $table->index(['client_id', 'appointment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
