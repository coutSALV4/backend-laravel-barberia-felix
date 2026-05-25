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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')
                ->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')
                ->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'card', 'transfer', 'other'])
                ->default('cash');
            $table->enum('status', ['pending', 'completed', 'refunded'])
                ->default('completed');
            $table->string('reference')->nullable();  // folio de tarjeta/transferencia
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
