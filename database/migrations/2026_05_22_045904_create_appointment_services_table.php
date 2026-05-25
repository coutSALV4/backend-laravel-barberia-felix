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
        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')
                ->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')
                ->constrained()->restrictOnDelete();
            $table->decimal('price_at_time', 10, 2);  // precio histórico

            $table->unique(['appointment_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_services');
    }
};
